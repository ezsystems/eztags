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
        return array( 'tags',
                      'tag_ids',
                      'id_string',
                      'keyword_string',
                      'meta_keyword_string',
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
            case 'tags' :
            {
                return $this->tags();
            } break;

            case 'tag_ids' :
            {
                return $this->IDArray;
            } break;

            case 'id_string' :
            {
                return $this->idString();
            } break;

            case 'keyword_string' :
            {
                return $this->keywordString();
            } break;

            case 'meta_keyword_string' :
            {
                return $this->keywordString( ", " );
            } break;

            case 'parent_string' :
            {
                return $this->parentString();
            } break;

            default:
            {
                eZDebug::writeError( "Attribute '$name' does not exist", "eZTags::attribute" );
                return null;
            } break;
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
        $idArray = explode( '|#', $idString );
        $keywordArray = explode( '|#', $keywordString );
        $parentArray = explode( '|#', $parentString );

        $wordArray = array();
        foreach ( array_keys( $idArray ) as $key )
        {
            $wordArray[] = trim( $idArray[$key] ) . "|#" . trim( $keywordArray[$key] ) . "|#" . trim( $parentArray[$key] );
        }

        $wordArray = array_unique( $wordArray );
        foreach ( $wordArray as $wordKey )
        {
            $word = explode( '|#', $wordKey );
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
        if ( !( $attribute instanceof eZContentObjectAttribute && is_numeric( $attribute->attribute( 'id' ) ) ) )
        {
            return;
        }

        $classAttribute = $attribute->contentClassAttribute();
        $maxTags = (int) $classAttribute->attribute( eZTagsType::MAX_TAGS_FIELD );
        if ( $maxTags > 0 && count( $this->IDArray ) > $maxTags )
        {
            $this->IDArray = array_slice( $this->IDArray, 0, $maxTags );
            $this->KeywordArray = array_slice( $this->KeywordArray, 0, $maxTags );
            $this->ParentArray = array_slice( $this->ParentArray, 0, $maxTags );
        }

        $db = eZDB::instance();
        $words = $db->arrayQuery( "SELECT eztags.id, eztags.keyword, eztags.parent_id FROM eztags_attribute_link, eztags
                                    WHERE eztags_attribute_link.keyword_id = eztags.id AND
                                    eztags_attribute_link.objectattribute_id = " . $attribute->attribute( 'id' ) . " AND
                                    eztags_attribute_link.objectattribute_version = " . $attribute->attribute( 'version' ) );

        $wordArray = array();
        foreach ( $words as $w )
        {
            $wordArray[] = trim( $w['id'] ) . "|#" . trim( $w['keyword'] ) . "|#" . trim( $w['parent_id'] );
        }

        $wordArray = array_unique( $wordArray );
        foreach ( $wordArray as $wordKey )
        {
            $word = explode( '|#', $wordKey );
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
        if ( !( $attribute instanceof eZContentObjectAttribute && is_numeric( $attribute->attribute( 'id' ) ) ) )
        {
            return;
        }

        $attributeID = $attribute->attribute( 'id' );
        $attributeVersion = $attribute->attribute( 'version' );
        $objectID = $attribute->attribute( 'contentobject_id' );

        $db = eZDB::instance();
        $currentTime = time();

        //get existing tags for object attribute
        $existingTagIDs = array();
        $existingTags = $db->arrayQuery( "SELECT DISTINCT keyword_id FROM eztags_attribute_link WHERE objectattribute_id = $attributeID AND objectattribute_version = $attributeVersion" );

        if ( is_array($existingTags ) )
        {
            foreach ( $existingTags as $t )
            {
                $existingTagIDs[] = (int) $t['keyword_id'];
            }
        }

        //get tags to delete from object attribute
        $tagsToDelete = array();
        $tempIDArray = array();

        // if for some reason already existing tags are added with ID = 0 with fromString
        // check to see if they really exist, so we don't delete them by mistake
        foreach ( array_keys( $this->IDArray ) as $key )
        {
            if ( $this->IDArray[$key] == 0 )
            {
                $existing = eZTagsObject::fetchList( array( 'keyword' => array( 'like', trim( $this->KeywordArray[$key] ) ), 'parent_id' => $this->ParentArray[$key] ) );
                if ( is_array( $existing ) && !empty( $existing ) )
                    $tempIDArray[] = $existing[0]->attribute( 'id' );
            }
            else
            {
                $tempIDArray[] = $this->IDArray[$key];
            }
        }

        foreach ( $existingTagIDs as $tid )
        {
            if ( !in_array( $tid, $tempIDArray ) )
            {
                $tagsToDelete[] = $tid;
            }
        }

        //and delete them
        if ( !empty( $tagsToDelete ) )
        {
            $dbString = $db->generateSQLINStatement( $tagsToDelete, 'keyword_id', false, true, 'int' );
            $db->query( "DELETE FROM eztags_attribute_link WHERE $dbString AND eztags_attribute_link.objectattribute_id = $attributeID AND eztags_attribute_link.objectattribute_version = $attributeVersion" );
        }

        //get tags that are new to the object attribute
        $newTags = array();
        $tagsToLink = array();
        foreach ( array_keys( $this->IDArray ) as $key )
        {
            if ( !in_array( $this->IDArray[$key], $existingTagIDs ) )
            {
                if ( $this->IDArray[$key] == 0 )
                {
                    // We won't allow adding tags to the database that already exist, but instead, we link to the existing tags
                    $existing = eZTagsObject::fetchList( array( 'keyword' => array( 'like', trim( $this->KeywordArray[$key] ) ), 'parent_id' => $this->ParentArray[$key] ) );

                    if ( is_array( $existing ) && !empty( $existing ) )
                    {
                        if ( !in_array( $existing[0]->attribute( 'id' ), $existingTagIDs ) )
                            $tagsToLink[] = $existing[0]->attribute( 'id' );
                    }
                    else
                    {
                        $newTags[] = array( 'id' => $this->IDArray[$key], 'keyword' => $this->KeywordArray[$key], 'parent_id' => $this->ParentArray[$key] );
                    }
                }
                else
                    $tagsToLink[] = $this->IDArray[$key];
            }
        }

        //we need to check if user really has access to tags/add, taking into account policy and subtree limits
        $attributeSubTreeLimit = $attribute->contentClassAttribute()->attribute( eZTagsType::SUBTREE_LIMIT_FIELD );
        $userLimitations = eZTagsTemplateFunctions::getSimplifiedUserAccess('tags', 'add');

        if ( $userLimitations['accessWord'] != 'no' && !empty( $newTags ) )
        {
            //first we need to fetch all locations user has access to
            $userLimitations = isset( $userLimitations['simplifiedLimitations']['Tag'] ) ? $userLimitations['simplifiedLimitations']['Tag'] : array();
            $allowedLocations = self::getAllowedLocations( $attributeSubTreeLimit, $userLimitations );

            foreach ( $newTags as $t )
            {
                //and then for each tag check if user can save in one of the allowed locations
                $parentTag = eZTagsObject::fetch( $t['parent_id'] );
                $pathString = ( $parentTag instanceof eZTagsObject ) ? $parentTag->attribute( 'path_string' ) : '/';
                $depth = ( $parentTag instanceof eZTagsObject ) ? (int) $parentTag->attribute( 'depth' ) + 1 : 1;

                if ( self::canSave( $pathString, $allowedLocations ) )
                {
                    $db->query( "INSERT INTO eztags ( parent_id, main_tag_id, keyword, depth, path_string, modified, remote_id ) VALUES ( " .
                                 $t['parent_id'] . ", 0, '" . $db->escapeString( trim( $t['keyword'] ) ) . "', $depth, '$pathString', 0, '" . eZTagsObject::generateRemoteID() . "' )" );
                    $tagID = (int) $db->lastSerialID( 'eztags', 'id' );
                    $db->query( "UPDATE eztags SET path_string = CONCAT(path_string, CAST($tagID AS CHAR), '/') WHERE id = $tagID" );

                    $pathArray = explode( '/', trim( $pathString, '/' ) );
                    array_push( $pathArray, $tagID );
                    $db->query( "UPDATE eztags SET modified = $currentTime WHERE " . $db->generateSQLINStatement( $pathArray, 'id', false, true, 'int' ) );

                    $tagsToLink[] = $tagID;

                    if ( class_exists( 'ezpEvent', false ) )
                        ezpEvent::getInstance()->filter( 'tag/add', array( 'tag' => eZTagsObject::fetch( $tagID ), 'parentTag' => $parentTag ) );
                }
            }
        }

        //link tags to objects taking into account subtree limit
        if ( !empty( $tagsToLink ) )
        {
            $dbString = $db->generateSQLINStatement( $tagsToLink, 'id', false, true, 'int' );
            $tagsToLink = $db->arrayQuery( "SELECT id, path_string FROM eztags WHERE $dbString" );

            if ( is_array( $tagsToLink ) && !empty( $tagsToLink ) )
            {
                foreach ( $tagsToLink as $t )
                {
                    if ( $attributeSubTreeLimit == 0 || ( $attributeSubTreeLimit > 0 &&
                         strpos( $t['path_string'], '/' . $attributeSubTreeLimit . '/' ) !== false ) )
                    {
                        $db->query( "INSERT INTO eztags_attribute_link ( keyword_id, objectattribute_id, objectattribute_version, object_id ) VALUES ( " . $t['id'] . ", $attributeID, $attributeVersion, $objectID )" );
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
    private static function getAllowedLocations( $attributeSubTreeLimit, $userLimitations )
    {
        if ( empty( $userLimitations ) )
            return array( (string) $attributeSubTreeLimit );
        else
        {
            if ( $attributeSubTreeLimit == 0 )
                return $userLimitations;
            else
            {
                $limitTag = eZTagsObject::fetch( $attributeSubTreeLimit );
                $pathString = ( $limitTag instanceof eZTagsObject ) ? $limitTag->attribute( 'path_string' ) : '/';

                foreach ( $userLimitations as $l )
                {
                    if ( strpos( $pathString, '/' . $l . '/' ) !== false )
                        return array( (string) $attributeSubTreeLimit );
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
    private static function canSave( $pathString, $allowedLocations )
    {
        if ( !empty( $allowedLocations ) )
        {
            if ( $allowedLocations[0] == 0 )
            {
                return true;
            }
            else
            {
                foreach ( $allowedLocations as $l )
                {
                    if ( strpos( $pathString, '/' . $l . '/' ) !== false )
                    {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Returns tags within this instance
     *
     * @return array
     */
    function tags()
    {
        if ( !is_array( $this->IDArray ) || empty( $this->IDArray ) )
            return array();

        return eZTagsObject::fetchList( array( 'id' => array( $this->IDArray ) ) );
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
        return implode( '|#', $this->IDArray );
    }

    /**
     * Returns the keywords as a string
     *
     * @return string
     */
    function keywordString( $separator = '|#' )
    {
        return implode( $separator, $this->KeywordArray );
    }

    /**
     * Returns the parent IDs as a string
     *
     * @return string
     */
    function parentString()
    {
        return implode( '|#', $this->ParentArray );
    }
}

?>
