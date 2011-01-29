<?php

/**
 * eZTags class implements functions used by eztags datatype
 * 
 */
class eZTags
{
    /**
     * Contains IDs of the tags
     * 
     * @var array $ParentArray
     */
    private $IDArray = array();

    /**
     * Contains the keywords in the same order as IDs
     * 
     * @var array $KeywordArray
     */
    private $KeywordArray = array();

    /**
     * Contains parent IDs in same order as IDs
     * 
     * @var array $ParentArray
     */
    private $ParentArray = array();

    /**
     * Returns an array with attributes that are available
     * 
     * @return array
     */
    function attributes()
    {
        return array( 'id_string',
                      'keyword_string',
                      'parent_string' );
    }

    /**
     * Returns true if the provided attribute exists
     * 
     * @param string $name
     * @return bool
     */
    function hasAttribute( $name )
    {
        return in_array( $name, $this->attributes() );
    }

    /**
     * Returns the specified attribute
     * 
     * @param string $name
     * @return mixed
     */
    function attribute( $name )
    {
        switch ( $name )
        {
            case 'id_string' :
            {
                return $this->idString();
            }break;

            case 'keyword_string' :
            {
                return $this->keywordString();
            }break;

            case 'parent_string' :
            {
                return $this->parentString();
            }break;

            default:
            {
                eZDebug::writeError( "Attribute '$name' does not exist", 'eZTags::attribute' );
                return null;
            }break;
        }
    }

    /**
     * Initializes the tags
     * 
     * @param string $idString
     * @param string $keywordString
     * @param string $parentString
     */
    function createFromStrings( $idString, $keywordString, $parentString )
    {
    	$idArray = explode( ', ', $idString );
        $keywordArray = explode( ', ', $keywordString );
        $parentArray = explode( ', ', $parentString );

        $wordArray = array();
        foreach ( array_keys( $idArray ) as $key )
        {
            $wordArray[] = trim( $idArray[$key] ) . "," . trim( $keywordArray[$key] ) . "," . trim($parentArray[$key]);
        }

        $wordArray = array_unique( $wordArray );
        foreach ( $wordArray as $wordKey )
        {
            $word = explode( ',', $wordKey );
            if ($word[0] != '')
            {
            	$this->IDArray[] = (int) $word[0];
                $this->KeywordArray[] = $word[1];
                $this->ParentArray[] = (int) $word[2];
            }
        }
    }

    /**
     * Fetches the tags for the given attribute
     * 
     * @param eZContentObjectAttribute $attribute
     */
    function createFromAttribute( $attribute )
    {
        if ( $attribute->attribute( 'id' ) === null )
        {
            return;
        }

        $db = eZDB::instance();
        $words = $db->arrayQuery( "SELECT eztags.id, eztags.keyword, eztags.parent_id FROM eztags_attribute_link, eztags
                                    WHERE eztags_attribute_link.keyword_id = eztags.id AND
                                    eztags_attribute_link.objectattribute_id = " . $attribute->attribute( 'id' ) );

        $wordArray = array();
        foreach ( $words as $w )
        {
            $wordArray[] = trim($w['id']) . "," . trim($w['keyword']) . "," . trim($w['parent_id']);
        }

        $wordArray = array_unique( $wordArray );
        foreach ( $wordArray as $wordKey )
        {
            $word = explode( ',', $wordKey );
            if ($word[0] != '')
            {
            	$this->IDArray[] = (int) $word[0];
                $this->KeywordArray[] = $word[1];
                $this->ParentArray[] = (int) $word[2];
            }
        }
    }

    /**
     * Stores the tags to database
     * 
     * @param eZContentObjectAttribute $attribute
     */
    function store( $attribute )
    {
        $db = eZDB::instance();

		$attributeID = $attribute->attribute( 'id' );
		$objectID = $attribute->attribute( 'contentobject_id' );

		//get existing tags for object attribute
		$existingTagIDs = array();
		$existingTags = $db->arrayQuery( "SELECT DISTINCT keyword_id FROM eztags_attribute_link WHERE objectattribute_id = $attributeID" );
			
		if(is_array($existingTags))
		{
			foreach($existingTags as $t)
			{
				$existingTagIDs[] = (int) $t['keyword_id'];
			}
		}

		//get tags to delete from object attribute
		$tagsToDelete = array();
		foreach($existingTagIDs as $tid)
		{
			if(!in_array($tid, $this->IDArray))
			{
				$tagsToDelete[] = $tid;
			}
		}

		if(count($tagsToDelete) > 0)
		{
			$dbString = $db->generateSQLINStatement($tagsToDelete, 'keyword_id', false, true, 'int');
			$db->query( "DELETE FROM eztags_attribute_link WHERE $dbString AND eztags_attribute_link.objectattribute_id = $attributeID" );
		}

		//get tags that are new to the object attribute
		$newTags = array();
		foreach(array_keys($this->IDArray) as $key)
		{
			if(!in_array($this->IDArray[$key], $existingTagIDs))
			{
				$newTags[] = array('id' => $this->IDArray[$key], 'keyword' => $this->KeywordArray[$key], 'parent_id' => $this->ParentArray[$key]);
			}
		}

		//we need to check if user has add premissions taking subtree limitations and policies into account
		$hasAddAccess = false;
		$attributeSubTreeLimit = $attribute->contentClassAttribute()->attribute( eZTagsType::SUBTREE_LIMIT_FIELD );
		$subTreeLimitTag = eZTagsObject::fetch($attributeSubTreeLimit);

		$userLimitations = eZTagsTemplateFunctions::getSimplifiedUserAccess('tags', 'add');
		if($userLimitations['accessWord'] != 'no')
		{
			if(!isset($userLimitations['simplifiedLimitations']['Tag']))
			{
				$hasAddAccess = true;
			}
			else if($subTreeLimitTag instanceof eZTagsObject)
			{
				foreach($userLimitations['simplifiedLimitations']['Tag'] as $key => $value)
				{
					if(strpos($subTreeLimitTag->PathString, '/' . $value . '/') !== false)
					{
						$hasAddAccess = true;
						break;
					}
				}
			}
			else
			{
				$hasAddAccess = true;

				$attributeSubTreeLimitArray = array();
				foreach($userLimitations['simplifiedLimitations']['Tag'] as $key => $value)
				{
					$attributeSubTreeLimitArray[] = $value;
				}
			}
		}

		// Store every new tag
		foreach($newTags as $tag)
		{
			if($tag['id'] == 0)
			{
				//if tag doesn't exist yet, store it before linking to object attribute
				$keyword = $db->escapeString(trim($tag['keyword']));
				$parentID = $tag['parent_id'];

				$parentTag = eZTagsObject::fetch($parentID);
				$pathString = ($parentTag instanceof eZTagsObject) ? $parentTag->PathString : '/';

				$allowedBySubtreeLimit = false;
				if(isset($attributeSubTreeLimitArray))
				{
					foreach($attributeSubTreeLimitArray as $key => $value)
					{
						if($value > 0 && strpos($pathString, '/' . $value . '/') !== false)
						{
							$allowedBySubtreeLimit = true;
							break;
						}
					}
				}
				else if($attributeSubTreeLimit == 0 || ($attributeSubTreeLimit > 0 && strpos($pathString, '/' . $attributeSubTreeLimit . '/') !== false))
				{
					$allowedBySubtreeLimit = true;
				}

				if($allowedBySubtreeLimit && $hasAddAccess)
				{
					$current_time = time();
					$db->query( "INSERT INTO eztags ( parent_id, main_tag_id, keyword, path_string, modified ) VALUES ( $parentID, 0, '$keyword', '$pathString', $current_time )" );
					$tagID = (int) $db->lastSerialID( 'eztags', 'id' );
					$db->query( "UPDATE eztags SET path_string = CONCAT(path_string, CAST($tagID AS CHAR), '/') WHERE id = $tagID" );
					$pathString .= (string) $tagID . '/';
				}
			}
			else
			{
				$tagID = $tag['id'];
				$tagObject = eZTagsObject::fetch($tagID);
				$pathString = $tagObject->PathString;
			}

			if($attributeSubTreeLimit == 0 || ($attributeSubTreeLimit > 0 && strpos($pathString, '/' . $attributeSubTreeLimit . '/') !== false))
			{
				$db->query( "INSERT INTO eztags_attribute_link ( keyword_id, objectattribute_id, object_id ) VALUES ( $tagID, $attributeID, $objectID )" );
			}
		}
    }

    /**
     * Returns the tags ID array
     * 
     * @return array
     */
    function idArray()
    {
        return $this->IDArray;
    }

    /**
     * Returns the IDs as a string
     * 
     * @return string
     */
    function idString()
    {
        return implode( ', ', $this->IDArray );
    }

    /**
     * Returns the keywords as a string
     * 
     * @return string
     */
    function keywordString()
    {
        return implode( ', ', $this->KeywordArray );
    }

    /**
     * Returns the parent IDs as a string
     * 
     * @return string
     */
    function parentString()
    {
        return implode( ', ', $this->ParentArray );
    }
}

?>
