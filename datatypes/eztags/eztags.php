<?php

/**
 * eZTags class implements functions used by eztags datatype
 */
class eZTags
{
    /**
     * Contains structure of the tag IDs in the attribute
     *
     * @var array $IDArray
     */
    private $IDArray = array();

    /**
     * Contains structure of the tag keywords in the attribute
     *
     * @var array $KeywordArray
     */
    private $KeywordArray = array();

    /**
     * Contains structure of the tag parent IDs in the attribute
     *
     * @var array $ParentArray
     */
    private $ParentArray = array();

    /**
     * Contains structure of the tag locales in the attribute
     *
     * @var array $LocaleArray
     */
    private $LocaleArray = array();

    /**
     * The content object attribute the current tags belong to
     *
     * @var eZContentObjectAttribute $Attribute
     */
    private $Attribute = null;

    /**
     * Instantiates a new eZTags object
     *
     * @param eZContentObjectAttribute $attribute
     * @param array $idArray
     * @param array $keywordArray
     * @param array $parentArray
     * @param array $localeArray
     */
    private function __construct( $attribute, $idArray = array(), $keywordArray = array(), $parentArray = array(), $localeArray = array() )
    {
        $this->IDArray = $idArray;
        $this->KeywordArray = $keywordArray;
        $this->ParentArray = $parentArray;
        $this->LocaleArray = $localeArray;
        $this->Attribute = $attribute;
    }

    /**
     * Returns an array with attributes that are available
     *
     * @return array
     */
    function attributes()
    {
        return array( 'tags',
                      'permission_array',
                      'tag_ids',
                      'keywords',
                      'parent_ids',
                      'locales',
                      'id_string',
                      'keyword_string',
                      'parent_string',
                      'locale_string' );
    }

    /**
     * Returns true if the provided attribute exists
     *
     * @param string $name
     *
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
     *
     * @return mixed
     */
    function attribute( $name )
    {
        if ( !$this->hasAttribute( $name ) )
        {
            eZDebug::writeError( "Attribute '$name' does not exist", __METHOD__ );
            return null;
        }

        if ( $name == 'tags' )
            return $this->tags();
        else if ( $name == 'permission_array' )
            return $this->getPermissionArray();
        else if ( $name == 'tag_ids' )
            return $this->IDArray;
        else if ( $name == 'keywords' )
            return $this->KeywordArray;
        else if ( $name == 'parent_ids' )
            return $this->ParentArray;
        else if ( $name == 'locales' )
            return $this->LocaleArray;
        else if ( $name == 'id_string' )
            return $this->idString();
        else if ( $name == 'keyword_string' )
            return $this->keywordString();
        else if ( $name == 'parent_string' )
            return $this->parentString();
        else if ( $name == 'locale_string' )
            return $this->localeString();

        return null;
    }

    /**
     * Initializes the tags
     *
     * @static
     *
     * @param eZContentObjectAttribute $attribute
     * @param string $idString
     * @param string $keywordString
     * @param string $parentString
     * @param string $localeString
     *
     * @return eZTags The newly created eZTags object
     */
    static function createFromStrings( eZContentObjectAttribute $attribute, $idString, $keywordString, $parentString, $localeString )
    {
        $idArray = explode( '|#', $idString );
        $keywordArray = explode( '|#', $keywordString );
        $parentArray = explode( '|#', $parentString );
        $localeArray = explode( '|#', $localeString );

        $wordArray = array();
        foreach ( array_keys( $idArray ) as $key )
        {
            $wordArray[] = trim( $idArray[$key] ) . "|#" . trim( $keywordArray[$key] ) . "|#" . trim( $parentArray[$key] ) . "|#" . trim( $localeArray[$key] );
        }

        $idArray = array();
        $keywordArray = array();
        $parentArray = array();
        $localeArray = array();

        $db = eZDB::instance();

        $wordArray = array_unique( $wordArray );
        foreach ( $wordArray as $wordKey )
        {
            $word = explode( '|#', $wordKey );
            if ( $word[0] != '' )
            {
                $idArray[] = (int) $word[0];
                $keywordArray[] = $db->escapeString( trim( $word[1] ) );
                $parentArray[] = (int) $word[2];
                $localeArray[] = $db->escapeString( trim( $word[3] ) );
            }
        }

        return new self( $attribute, $idArray, $keywordArray, $parentArray, $localeArray );
    }

    /**
     * Fetches the tags for the given attribute and locale
     *
     * @static
     *
     * @param eZContentObjectAttribute $attribute
     * @param string|null $locale
     *
     * @return eZTags The newly created eZTags object
     */
    static function createFromAttribute( eZContentObjectAttribute $attribute, $locale = null )
    {
        $idArray = array();
        $keywordArray = array();
        $parentArray = array();
        $localeArray = array();

        if ( !is_numeric( $attribute->attribute( 'id' ) ) || !is_numeric( $attribute->attribute( 'version' ) ) )
            return new self( $attribute );

        if ( $locale === null || !is_string( $locale ) )
            $locale = $attribute->attribute( 'language_code' );

        // First fetch tags translated to defined locale
        $db = eZDB::instance();
        $words = $db->arrayQuery( "SELECT
                                       eztags.id,
                                       eztags_keyword.keyword,
                                       eztags.parent_id,
                                       eztags_keyword.locale
                                   FROM eztags_attribute_link, eztags, eztags_keyword
                                   WHERE eztags_attribute_link.keyword_id = eztags.id AND
                                       eztags.id = eztags_keyword.keyword_id AND eztags_keyword.locale = '" .
                                       $db->escapeString( $locale ) . "' AND
                                       eztags_keyword.status = " . eZTagsKeyword::STATUS_PUBLISHED . " AND
                                       eztags_attribute_link.objectattribute_id = " . $attribute->attribute( 'id' ) . " AND
                                       eztags_attribute_link.objectattribute_version = " . $attribute->attribute( 'version' ) );

        $wordArray = array();
        foreach ( $words as $w )
        {
            $wordArray[] = trim( $w['id'] ) . "|#" . trim( $w['keyword'] ) . "|#" . trim( $w['parent_id'] ) . "|#" . trim( $w['locale'] );
        }

        $wordArray = array_unique( $wordArray );
        foreach ( $wordArray as $wordKey )
        {
            $word = explode( '|#', $wordKey );
            if ( $word[0] != '' )
            {
                $idArray[] = (int) $word[0];
                $keywordArray[] = $db->escapeString( trim( $word[1] ) );
                $parentArray[] = (int) $word[2];
                $localeArray[] = $db->escapeString( trim( $word[3] ) );
            }
        }

        // Next, fetch untranslated tags
        $dbString = '';
        if ( !empty( $idArray ) )
            $dbString = $db->generateSQLINStatement( $idArray, 'eztags.id', true, true, 'int' ) . ' AND ';

        $words = $db->arrayQuery( "SELECT
                                       eztags.id,
                                       eztags_keyword.keyword,
                                       eztags.parent_id,
                                       eztags_keyword.locale
                                   FROM eztags_attribute_link, eztags, eztags_keyword
                                   WHERE eztags_attribute_link.keyword_id = eztags.id AND
                                       eztags.id = eztags_keyword.keyword_id AND
                                       eztags.main_language_id + MOD( eztags.language_mask, 2 ) = eztags_keyword.language_id AND
                                       eztags_keyword.status = " . eZTagsKeyword::STATUS_PUBLISHED . " AND $dbString
                                       eztags_attribute_link.objectattribute_id = " . $attribute->attribute( 'id' ) . " AND
                                       eztags_attribute_link.objectattribute_version = " . $attribute->attribute( 'version' ) );

        $wordArray = array();
        foreach ( $words as $w )
        {
            $wordArray[] = trim( $w['id'] ) . "|#" . trim( $w['keyword'] ) . "|#" . trim( $w['parent_id'] ) . "|#" . trim( $w['locale'] );
        }

        $wordArray = array_unique( $wordArray );
        foreach ( $wordArray as $wordKey )
        {
            $word = explode( '|#', $wordKey );
            if ( $word[0] != '' )
            {
                $idArray[] = (int) $word[0];
                $keywordArray[] = $db->escapeString( trim( $word[1] ) );
                $parentArray[] = (int) $word[2];
                $localeArray[] = $db->escapeString( trim( $word[3] ) );
            }
        }

        return new self( $attribute, $idArray, $keywordArray, $parentArray, $localeArray );
    }

    /**
     * Stores the tags to database
     *
     * @param eZContentObjectAttribute $attribute
     */
    function store( eZContentObjectAttribute $attribute )
    {
        $this->Attribute = $attribute;

        if( !is_numeric( $this->Attribute->attribute( 'id' ) ) || !is_numeric( $this->Attribute->attribute( 'version' ) ) )
            return;

        $db = eZDB::instance();
        $db->begin();

        // first remove all links in this version, allows emptying the attribute and storing
        eZTagsAttributeLinkObject::removeByAttribute( $this->Attribute->attribute( 'id' ), $this->Attribute->attribute( 'version' ) );

        if ( empty( $this->IDArray ) )
        {
            $db->commit();
            return;
        }

        // if for some reason already existing tags are added with ID = 0 with fromString
        // check to see if they really exist, so we can link to them
        // locale doesn't matter here, since we don't allow storing tags under the same
        // parent that have any of translations same as any other translation from another tag
        foreach ( array_keys( $this->IDArray ) as $key )
        {
            if ( $this->IDArray[$key] == 0 )
            {
                $results = $db->arrayQuery( "SELECT eztags.id
                                             FROM eztags, eztags_keyword
                                             WHERE eztags.id = eztags_keyword.keyword_id AND
                                                 eztags.parent_id = " . $this->ParentArray[$key] . " AND
                                                 eztags_keyword.keyword LIKE '" . $this->KeywordArray[$key] . "'",
                                            array( 'offset' => 0, 'limit' => 1 ) );

                if ( is_array( $results ) && !empty( $results ) )
                    $this->IDArray[$key] = (int) $results[0]['id'];
            }
        }

        // first check if user can really add tags, considering policies
        // and subtree limits
        $permissionArray = $this->getPermissionArray();

        foreach ( array_keys( $this->IDArray ) as $key )
        {
            if ( $this->IDArray[$key] == 0 && $permissionArray['can_add'] )
            {
                $pathString = '/';
                $depth = 0;

                $parentTag = eZTagsObject::fetchWithMainTranslation( $this->ParentArray[$key] );
                if ( $parentTag instanceof eZTagsObject )
                {
                    $pathString = $parentTag->attribute( 'path_string' );
                    $depth = (int) $parentTag->attribute( 'depth' );
                }

                //and then for each tag check if user can save in one of the allowed locations
                if ( self::canSave( $pathString, $permissionArray['allowed_locations'] ) )
                {
                    self::createAndLinkTag( $this->Attribute,
                                            $this->ParentArray[$key],
                                            $pathString,
                                            $depth,
                                            $this->KeywordArray[$key],
                                            $this->LocaleArray[$key] );
                }
            }
            else if ( $this->IDArray[$key] > 0 )
            {
                $tagObject = eZTagsObject::fetchWithMainTranslation( $this->IDArray[$key] );
                if ( !$tagObject instanceof eZTagsObject )
                    continue;

                if ( $permissionArray['subtree_limit'] == 0 || ( $permissionArray['subtree_limit'] > 0 &&
                     strpos( $tagObject->attribute( 'path_string' ), '/' . $permissionArray['subtree_limit'] . '/' ) !== false ) )
                {
                    self::linkTag( $this->Attribute, $tagObject, $this->KeywordArray[$key], $this->LocaleArray[$key] );
                }
            }
        }

        $db->commit();
    }

    /**
     * Returns the array with permission info for linking tags to current content object attribute
     *
     * @return array
     */
    function getPermissionArray()
    {
        $permissionArray = array(
            'can_add'           => false,
            'subtree_limit'     => $this->Attribute->contentClassAttribute()->attribute( eZTagsType::SUBTREE_LIMIT_FIELD ),
            'allowed_locations' => array(),
            'allowed_locations_tags' => false );

        $userLimitations = eZTagsTemplateFunctions::getSimplifiedUserAccess( 'tags', 'add' );

        if ( $userLimitations['accessWord'] == 'no' )
            return $permissionArray;

        $userLimitations = isset( $userLimitations['simplifiedLimitations']['Tag'] ) ? $userLimitations['simplifiedLimitations']['Tag'] : array();
        $limitTag = eZTagsObject::fetchWithMainTranslation( $permissionArray['subtree_limit'] );

        if ( empty( $userLimitations ) )
        {
            if ( $permissionArray['subtree_limit'] == 0 || $limitTag instanceof eZTagsObject )
            {
                $permissionArray['allowed_locations'] = array( $permissionArray['subtree_limit'] );

                if ( $limitTag instanceof eZTagsObject )
                    $permissionArray['allowed_locations_tags'] = array( $limitTag );
            }
        }
        else if ( $permissionArray['subtree_limit'] == 0 )
        {
            $permissionArray['allowed_locations_tags'] = array();

            $userLimitations = eZTagsObject::fetchList( array( 'id' => array( $userLimitations ) ), null, null, true );
            if ( is_array( $userLimitations ) && !empty( $userLimitations ) )
            {
                foreach ( $userLimitations as $limitation )
                {
                    $permissionArray['allowed_locations'][] = $limitation->attribute( 'id' );
                    $permissionArray['allowed_locations_tags'][] = $limitation;
                }
            }
        }
        else if ( $limitTag instanceof eZTagsObject )
        {
            $userLimitations = eZTagsObject::fetchList( array( 'id' => array( $userLimitations ) ), null, null, true );
            if ( is_array( $userLimitations ) && !empty( $userLimitations ) )
            {
                $pathString = $limitTag->attribute( 'path_string' );
                foreach ( $userLimitations as $limitation )
                {
                    if ( strpos( $pathString, '/' . $limitation->attribute( 'id' ) . '/' ) !== false )
                    {
                        $permissionArray['allowed_locations'] = array( $permissionArray['subtree_limit'] );
                        $permissionArray['allowed_locations_tags'] = array( $limitTag );
                        break;
                    }
                }
            }
        }

        if ( !empty( $permissionArray['allowed_locations'] ) )
            $permissionArray['can_add'] = true;

        return $permissionArray;
    }

    /**
     * Checks if tags can be saved below tag with provided path string,
     * taking into account allowed locations for tag placement
     *
     * @static
     *
     * @param string $pathString
     * @param array $allowedLocations
     *
     * @return bool
     */
    private static function canSave( $pathString, $allowedLocations )
    {
        foreach ( $allowedLocations as $location )
        {
            if ( $location == 0 || strpos( $pathString, '/' . $location . '/' ) !== false )
                return true;
        }

        return false;
    }

    /**
     * Creates a new tag and links it to provided content object attribute
     *
     * @static
     *
     * @param eZContentObjectAttribute $attribute
     * @param int $parentID
     * @param string $parentPathString
     * @param int $parentDepth
     * @param string $keyword
     * @param string $locale
     */
    private static function createAndLinkTag( $attribute, $parentID, $parentPathString, $parentDepth, $keyword, $locale )
    {
        $languageID = eZContentLanguage::idByLocale( $locale );
        if ( $languageID === false )
            return;

        // we'll set the tag as always available, hence 'language_mask' => $languageID + 1
        // maybe allow configuration of this in future
        $tagObject = new eZTagsObject( array(
            'parent_id'        => $parentID,
            'main_tag_id'      => 0,
            'depth'            => $parentDepth + 1,
            'path_string'      => $parentPathString,
            'main_language_id' => $languageID,
            'language_mask'    => $languageID + 1 ), $locale );
        $tagObject->store();

        $tagObject->setAttribute( 'path_string', $tagObject->attribute( 'path_string' ) . $tagObject->attribute( 'id' ) . '/' );
        $tagObject->store();
        $tagObject->updateModified();

        $tagKeywordObject = new eZTagsKeyword( array(
            'keyword_id'  => $tagObject->attribute( 'id' ),
            'language_id' => $languageID + 1,
            'keyword'     => $keyword,
            'locale'      => $locale,
            'status'      => eZTagsKeyword::STATUS_PUBLISHED ) );
        $tagKeywordObject->store();

        $linkObject = new eZTagsAttributeLinkObject( array(
            'keyword_id'              => $tagObject->attribute( 'id' ),
            'objectattribute_id'      => $attribute->attribute( 'id' ),
            'objectattribute_version' => $attribute->attribute( 'version' ),
            'object_id'               => $attribute->attribute( 'contentobject_id' ) ) );
        $linkObject->store();
    }

    /**
     * Links the content object attribute and tag
     *
     * @static
     *
     * @param eZContentObjectAttribute $attribute
     * @param eZTagsObject $tagObject
     * @param string $keyword
     * @param string $locale
     */
    private static function linkTag( eZContentObjectAttribute $attribute, $tagObject, $keyword, $locale )
    {
        $languageID = eZContentLanguage::idByLocale( $locale );
        if ( $languageID === false )
            return;

        if ( $locale == $attribute->attribute( 'language_code' ) )
        {
            if ( !$tagObject->hasTranslation( $locale ) )
            {
                $tagKeywordObject = new eZTagsKeyword( array(
                    'keyword_id'  => $tagObject->attribute( 'id' ),
                    'language_id' => $languageID,
                    'keyword'     => $keyword,
                    'locale'      => $locale,
                    'status'      => eZTagsKeyword::STATUS_PUBLISHED ) );
                $tagKeywordObject->store();
                $tagObject->updateLanguageMask();
            }
        }

        $linkObject = new eZTagsAttributeLinkObject( array(
            'keyword_id'              => $tagObject->attribute( 'id' ),
            'objectattribute_id'      => $attribute->attribute( 'id' ),
            'objectattribute_version' => $attribute->attribute( 'version' ),
            'object_id'               => $attribute->attribute( 'contentobject_id' ) ) );
        $linkObject->store();
    }

    /**
     * Returns tags within this instance
     *
     * @return eZTagsObject[]
     */
    function tags()
    {
        if ( !is_array( $this->IDArray ) || empty( $this->IDArray ) )
            return array();

        return eZTagsObject::fetchList( array( 'id' => array( $this->IDArray ) ) );
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
     * @param string $separator
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

    /**
     * Returns tag locales as a string
     *
     * @return string
     */
    function localeString()
    {
        return implode( '|#', $this->LocaleArray );
    }
}

?>
