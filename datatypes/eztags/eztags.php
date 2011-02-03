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
        return array( 'tag_ids',
                      'id_string',
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
            case 'tag_ids' :
            {
                return $this->IDArray;
            }break;

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
		$attributeID = $attribute->attribute( 'id' );
		$objectID = $attribute->attribute( 'contentobject_id' );

		if(!(is_numeric($attributeID) && $attributeID > 0))
		{
			return;
		}

		$db = eZDB::instance();
		$currentTime = time();

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

		//and delete them
		if(count($tagsToDelete) > 0)
		{
			$dbString = $db->generateSQLINStatement($tagsToDelete, 'keyword_id', false, true, 'int');
			$db->query( "DELETE FROM eztags_attribute_link WHERE $dbString AND eztags_attribute_link.objectattribute_id = $attributeID" );
		}

		//get tags that are new to the object attribute
		$newTags = array();
		$tagsToLink = array();
		foreach(array_keys($this->IDArray) as $key)
		{
			if(!in_array($this->IDArray[$key], $existingTagIDs))
			{
				if($this->IDArray[$key] == 0)
					$newTags[] = array('id' => $this->IDArray[$key], 'keyword' => $this->KeywordArray[$key], 'parent_id' => $this->ParentArray[$key]);
				else
					$tagsToLink[] = $this->IDArray[$key];
			}
		}

		//we need to check if user really has access to tags/add, taking into account policy and subtree limits
		$attributeSubTreeLimit = $attribute->contentClassAttribute()->attribute( eZTagsType::SUBTREE_LIMIT_FIELD );
		$userLimitations = eZTagsTemplateFunctions::getSimplifiedUserAccess('tags', 'add');

		if($userLimitations['accessWord'] != 'no' && count($newTags) > 0)
		{
			//first we need to fetch all locations user has access to
			$userLimitations = isset($userLimitations['simplifiedLimitations']['Tag']) ? $userLimitations['simplifiedLimitations']['Tag'] : array();
			$allowedLocations = eZTags::getAllowedLocations($attributeSubTreeLimit, $userLimitations);

			foreach($newTags as $t)
			{
				//and then for each tag check if user can save in one of the allowed locations
				$parentTag = eZTagsObject::fetch($t['parent_id']);
				$pathString = ($parentTag instanceof eZTagsObject) ? $parentTag->PathString : '/';

				if(eZTags::canSave($pathString, $allowedLocations))
				{
					$db->query( "INSERT INTO eztags ( parent_id, main_tag_id, keyword, path_string, modified ) VALUES ( " .
						$t['parent_id'] . ", 0, '" . $db->escapeString(trim($t['keyword'])) . "', '$pathString', $currentTime )" );
					$tagID = (int) $db->lastSerialID( 'eztags', 'id' );
					$db->query( "UPDATE eztags SET path_string = CONCAT(path_string, CAST($tagID AS CHAR), '/') WHERE id = $tagID" );
					$tagsToLink[] = $tagID;
				}
			}
		}

		//link tags to objects taking into account subtree limit
		if(count($tagsToLink) > 0)
		{
			$dbString = $db->generateSQLINStatement($tagsToLink, 'id', false, true, 'int');
			$tagsToLink = $db->arrayQuery( "SELECT id, path_string FROM eztags WHERE $dbString" );

			if(is_array($tagsToLink) && count($tagsToLink) > 0)
			{
				foreach($tagsToLink as $t)
				{
					if($attributeSubTreeLimit == 0 || ($attributeSubTreeLimit > 0 &&
						strpos($t['path_string'], '/' . $attributeSubTreeLimit . '/') !== false))
					{
						$db->query( "INSERT INTO eztags_attribute_link ( keyword_id, objectattribute_id, object_id ) VALUES ( " . $t['id'] . ", $attributeID, $objectID )" );
					}
				}
			}
		}
    }


    /**
     * Returns all allowed locations user has access to
     *
     * @static
     * @param int $attributeSubTreeLimit
     * @param array $userLimitations
     * @return bool
     */
	private static function getAllowedLocations($attributeSubTreeLimit, $userLimitations)
	{
		if(count($userLimitations) == 0)
			return array((string) $attributeSubTreeLimit);
		else
		{
			if($attributeSubTreeLimit == 0)
				return $userLimitations;
			else
			{
				$limitTag = eZTagsObject::fetch($attributeSubTreeLimit);
				$pathString = ($limitTag instanceof eZTagsObject) ? $limitTag->PathString : '/';

				foreach($userLimitations as $l)
				{
					if(strpos($pathString, '/' . $l . '/') !== false)
						return array((string) $attributeSubTreeLimit);
				}
			}
		}

		return array();
	}

    /**
     * Checks if tag (described by its path string) can be saved
     * to one of the allowed locations
     *
     * @static
     * @param string $pathString
     * @param array $userLimitations
     * @return bool
     */
	private static function canSave($pathString, $allowedLocations)
	{
		if(count($allowedLocations) > 0)
		{
			if($allowedLocations[0] == 0)
			{
				return true;
			}
			else
			{
				foreach($allowedLocations as $l)
				{
					if(strpos($pathString, '/' . $l . '/') !== false)
					{
						return true;
					}
				}
			}
		}

		return false;
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
