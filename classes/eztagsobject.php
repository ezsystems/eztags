<?php

/**
 * eZTagsObject class inherits eZPersistentObject class
 * to be able to access eztags database table through API
 *
 */
class eZTagsObject extends eZPersistentObject
{
    /**
     * Constructor
     *
     */
    function __construct( $row, $locale = false )
    {
        $this->CurrentLanguage = $locale;
        parent::__construct( $row );
    }

    /**
     * Returns the definition array for eZTagsObject
     *
     * @return array
     */
    static function definition()
    {
        return array( 'fields'              => array( 'id'               => array( 'name'     => 'ID',
                                                                                   'datatype' => 'integer',
                                                                                   'default'  => 0,
                                                                                   'required' => true ),
                                                      'parent_id'        => array( 'name'     => 'ParentID',
                                                                                   'datatype' => 'integer',
                                                                                   'default'  => null,
                                                                                   'required' => false ),
                                                      'main_tag_id'      => array( 'name'     => 'MainTagID',
                                                                                   'datatype' => 'integer',
                                                                                   'default'  => null,
                                                                                   'required' => false ),
                                                      'keyword'          => array( 'name'     => 'Keyword',
                                                                                   'datatype' => 'string',
                                                                                   'default'  => '',
                                                                                   'required' => false ),
                                                      'depth'            => array( 'name'     => 'Depth',
                                                                                   'datatype' => 'integer',
                                                                                   'default'  => 1,
                                                                                   'required' => false ),
                                                      'path_string'      => array( 'name'     => 'PathString',
                                                                                   'datatype' => 'string',
                                                                                   'default'  => '',
                                                                                   'required' => false ),
                                                      'modified'         => array( 'name'     => 'Modified',
                                                                                   'datatype' => 'integer',
                                                                                   'default'  => 0,
                                                                                   'required' => false ),
                                                      'main_language_id' => array( 'name'     => 'MainLanguageID',
                                                                                   'datatype' => 'integer',
                                                                                   'default'  => 0,
                                                                                   'required' => false ),
                                                      'language_mask'    => array( 'name'     => 'LanguageMask',
                                                                                   'datatype' => 'integer',
                                                                                   'default'  => 0,
                                                                                   'required' => false ) ),
                      'function_attributes' => array( 'parent'                    => 'getParent',
                                                      'children'                  => 'getChildren',
                                                      'children_count'            => 'getChildrenCount',
                                                      'related_objects'           => 'getRelatedObjects',
                                                      'subtree_limitations'       => 'getSubTreeLimitations',
                                                      'subtree_limitations_count' => 'getSubTreeLimitationsCount',
                                                      'main_tag'                  => 'getMainTag',
                                                      'synonyms'                  => 'getSynonyms',
                                                      'synonyms_count'            => 'getSynonymsCount',
                                                      'icon'                      => 'getIcon',
                                                      'url'                       => 'getUrl',
                                                      'keyword'                   => 'getKeyword',
                                                      'available_languages'       => 'getAvailableLanguages',
                                                      'current_language'          => 'getCurrentLanguage',
                                                      'language_name_array'       => 'languageNameArray',
                                                      'main_translation'          => 'getMainTranslation',
                                                      'translations'              => 'getTranslations',
                                                      'translations_count'        => 'getTranslationsCount',
                                                      'always_available'          => 'isAlwaysAvailable' ),
                      'keys'                => array( 'id' ),
                      'increment_key'       => 'id',
                      'class_name'          => 'eZTagsObject',
                      'sort'                => array( 'keyword' => 'asc' ),
                      'name'                => 'eztags' );
    }

    /**
     * Updates path string of the tag and all of it's children and synonyms.
     *
     */
    function updatePathString()
    {
        $parentTag = $this->getParent( true );
        $pathStringPrefix = $parentTag instanceof eZTagsObject ? $parentTag->attribute( 'path_string' ) : '/';

        $this->setAttribute( 'path_string', $pathStringPrefix . $this->attribute( 'id' ) . '/' );
        $this->store();

        foreach ( $this->getSynonyms( true ) as $s )
        {
            $s->setAttribute( 'path_string', $pathStringPrefix . $s->attribute( 'id' ) . '/' );
            $s->store();
        }

        foreach ( $this->getChildren( true ) as $c )
        {
            $c->updatePathString();
        }
    }

    /**
     * Updates depth of the tag and all of it's children and synonyms.
     *
     */
    function updateDepth()
    {
        $parentTag = $this->getParent( true );
        $depth = $parentTag instanceof eZTagsObject ? $parentTag->attribute( 'depth' ) + 1 : 1;

        $this->setAttribute( 'depth', $depth );
        $this->store();

        foreach ( $this->getSynonyms( true ) as $s )
        {
            $s->setAttribute( 'depth', $depth );
            $s->store();
        }

        foreach ( $this->getChildren( true ) as $c )
        {
            $c->updateDepth();
        }
    }

    /**
     * Returns whether tag has a parent
     *
     * @return bool
     */
    function hasParent( $mainTranslation = false )
    {
        return $this->getParent( $mainTranslation ) instanceof eZTagsObject;
    }

    /**
     * Returns tag parent
     *
     * @return eZTagsObject
     */
    function getParent( $mainTranslation = false )
    {
        if ( $mainTranslation )
            return self::fetchWithMainTranslation( $this->attribute( 'parent_id' ) );

        return self::fetch( $this->attribute( 'parent_id' ) );
    }

    /**
     * Returns first level children tags
     *
     * @return array
     */
    function getChildren( $mainTranslation = false )
    {
        return self::fetchByParentID( $this->attribute( 'id' ), $mainTranslation );
    }

    /**
     * Returns count of first level children tags
     *
     * @return integer
     */
    function getChildrenCount( $mainTranslation = false )
    {
        return self::childrenCountByParentID( $this->attribute( 'id' ), $mainTranslation );
    }

    /**
     * Returns objects related to this tag
     *
     * @return array
     */
    function getRelatedObjects()
    {
        // Not an easy task to fetch published objects with API and take care of current_version, status
        // and attribute version, so just use SQL to fetch all related object ids in one go
        $tagID = (int) $this->attribute( 'id' );

        $db = eZDB::instance();
        $result = $db->arrayQuery( "SELECT DISTINCT(o.id) AS object_id FROM eztags_attribute_link l
                                   INNER JOIN ezcontentobject o ON l.object_id = o.id
                                   AND l.objectattribute_version = o.current_version
                                   AND o.status = " . eZContentObject::STATUS_PUBLISHED . "
                                   WHERE l.keyword_id = $tagID" );

        if ( is_array( $result ) && !empty( $result ) )
        {
            $objectIDArray = array();
            foreach ( $result as $r )
            {
                array_push( $objectIDArray, $r['object_id'] );
            }

            return eZContentObject::fetchIDArray( $objectIDArray );
        }

        return array();
    }

    /**
     * Returns list of eZContentClassAttribute objects (represented as subtree limitations)
     *
     * @return array
     */
    function getSubTreeLimitations()
    {
        if ( $this->attribute( 'main_tag_id' ) == 0 )
        {
            return eZPersistentObject::fetchObjectList( eZContentClassAttribute::definition(), null,
                                                        array( 'data_type_string'              => 'eztags',
                                                               eZTagsType::SUBTREE_LIMIT_FIELD => $this->attribute( 'id' ),
                                                               'version'                       => eZContentClass::VERSION_STATUS_DEFINED ) );
        }

        return array();
    }

    /**
     * Returns count of eZContentClassAttribute objects (represented as subtree limitation count)
     *
     * @return integer
     */
    function getSubTreeLimitationsCount()
    {
        if ( $this->attribute( 'main_tag_id' ) == 0 )
        {
            return eZPersistentObject::count( eZContentClassAttribute::definition(),
                                              array( 'data_type_string'              => 'eztags',
                                                     eZTagsType::SUBTREE_LIMIT_FIELD => $this->attribute( 'id' ),
                                                     'version'                       => eZContentClass::VERSION_STATUS_DEFINED ) );
        }

        return 0;
    }

    /**
     * Checks if any of the parents have subtree limits defined
     *
     * @return bool
     */
    function isInsideSubTreeLimit()
    {
        $tag = $this;
        while ( $tag->hasParent( true ) )
        {
            $tag = $tag->getParent( true );
            if ( $tag->getSubTreeLimitationsCount() > 0 )
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the main tag for synonym
     *
     * @return eZTagsObject
     */
    function getMainTag( $mainTranslation = false )
    {
        if ( $mainTranslation )
            return self::fetchWithMainTranslation( $this->attribute( 'main_tag_id' ) );

           return self::fetch( $this->attribute( 'main_tag_id' ) );
    }

    /**
     * Returns synonyms for the tag
     *
     * @return array
     */
    function getSynonyms( $mainTranslation = false )
    {
        return self::fetchSynonyms( $this->attribute( 'id' ), $mainTranslation );
    }

    /**
     * Returns synonym count for the tag
     *
     * @return array
     */
    function getSynonymsCount( $mainTranslation = false )
    {
        return self::synonymsCount( $this->attribute( 'id' ), $mainTranslation );
    }

    /**
     * Returns all links to objects for this tag
     *
     * @return array
     */
    function getTagAttributeLinks()
    {
        return eZTagsAttributeLinkObject::fetchByTagID( $this->attribute( 'id' ) );
    }

    /**
     * Returns icon associated with the tag, while respecting hierarchy structure
     *
     * @return string
     */
    function getIcon()
    {
        $ini = eZINI::instance( 'eztags.ini' );
        $iconMap = $ini->variable( 'Icons', 'IconMap' );
        $defaultIcon = $ini->variable( 'Icons', 'Default' );

        if ( $this->attribute( 'main_tag_id' ) > 0 )
            $tag = $this->getMainTag( true );
        else
            $tag = $this;

        if ( array_key_exists( $tag->attribute( 'id' ), $iconMap ) && !empty( $iconMap[$tag->attribute( 'id' )] ) )
            return $iconMap[$tag->attribute( 'id' )];

        while ( $tag->hasParent( true ) )
        {
            $tag = $tag->getParent( true );
            if ( array_key_exists( $tag->attribute( 'id' ), $iconMap ) && !empty( $iconMap[$tag->attribute( 'id' )] ) )
                return $iconMap[$tag->attribute( 'id' )];
        }

        return $defaultIcon;
    }

    /**
     * Returns the URL of the tag, to be used in tags/view
     *
     * @return string
     */
    function getUrl()
    {
        $url = urlencode( $this->attribute( 'keyword' ) );
        $tag = $this;

        while ( $tag->hasParent( true ) )
        {
            $tag = $tag->getParent( true );
            $url = urlencode( $tag->attribute( 'keyword' ) ) . '/' . $url;
        }

        return $url;
    }

    /**
     * Updates modified timestamp on current tag and all of its parents
     * Expensive to run through API, so SQL takes care of it
     *
     */
    function updateModified()
    {
        $pathArray = explode( '/', trim( $this->attribute( 'path_string' ), '/' ) );

        if ( $this->attribute( 'main_tag_id' ) > 0 )
            array_push( $pathArray, $this->attribute( 'main_tag_id' ) );

        if ( !empty( $pathArray ) )
        {
            $db = eZDB::instance();
            $db->query( "UPDATE eztags SET modified = " . time() .
                        " WHERE " . $db->generateSQLINStatement( $pathArray, 'id', false, true, 'int' ) );
        }
    }

    /**
     * Registers all objects related to this tag to search engine for processing
     *
     */
    function registerSearchObjects()
    {
        $eZTagsINI = eZINI::instance( 'eztags.ini' );

        if ( eZINI::instance( 'site.ini' )->variable( 'SearchSettings', 'DelayedIndexing' ) == 'enabled'
            || $eZTagsINI->variable( 'SearchSettings', 'ReindexWhenDelayedIndexingDisabled' ) == 'enabled' )
        {
            $relatedObjects = $this->getRelatedObjects();
            foreach ( $relatedObjects as $relatedObject )
            {
                eZContentOperationCollection::registerSearchObject( $relatedObject->attribute( 'id' ), $relatedObject->attribute( 'current_version' ) );
            }

            return;
        }

        eZHTTPTool::instance()->setSessionVariable( 'eZTagsShowReindexMessage', 1 );
    }

    /**
     * Returns eZTagsObject for given ID
     *
     * @static
     * @param integer $id
     * @return eZTagsObject
     */
    static function fetch( $id )
    {
        $tag = eZPersistentObject::fetchObject( self::definition(), null, array( 'id' => $id ) );
        if ( $tag instanceof eZTagsObject )
            return $tag->fetchTranslated();

        return false;
    }

    static function fetchByLocale( $id, $locale = false, $forceLoad = false )
    {
        $tag = eZPersistentObject::fetchObject( self::definition(), null, array( 'id' => $id ) );
        if ( $tag instanceof eZTagsObject )
            return $tag->fetchTranslated( false, $locale, $forceLoad );

        return false;
    }

    static function fetchWithMainTranslation( $id )
    {
        $tag = eZPersistentObject::fetchObject( self::definition(), null, array( 'id' => $id ) );
        if ( $tag instanceof eZTagsObject )
            return $tag->fetchTranslated( true );

        return false;
    }

    /**
     * Returns array of eZTagsObject objects for given params
     *
     * @static
     * @param array $params
     * @param array $limits
     * @return array
     */
    static function fetchList( $params, $limits = null, $asObject = true, $sorts = null, $mainTranslation = false )
    {
        $customConds = null;
        if ( !$mainTranslation )
        {
            if ( is_array( $params ) && !empty( $params ) )
                $customConds = ' AND ( ' . eZContentLanguage::languagesSQLFilter( 'eztags' ) . ' ) ';
            else
                $customConds = ' WHERE ( ' . eZContentLanguage::languagesSQLFilter( 'eztags' ) . ' ) ';
        }

        $tagsList = eZPersistentObject::fetchObjectList( self::definition(), null, $params,
                                                         $sorts, $limits, true, false, null,
                                                         null, $customConds );

        $tagsList = eZTagsObject::processTagsForTranslations( $tagsList );

        if ( $asObject )
            return $tagsList;

        $tagsArray = array();
        foreach ( $tagsList as $tag )
            $tagsArray[] = array( 'name' => $tag->attribute( 'keyword' ), 'id' => $tag->attribute( 'id' ) );

        return $tagsArray;
    }

    /**
     * Returns count of eZTagsObject objects for given params
     *
     * @static
     * @param mixed $params
     * @return integer
     */
    static function fetchListCount( $params, $mainTranslation = false )
    {
        $customConds = null;
        if ( !$mainTranslation )
        {
            if ( is_array( $params ) && !empty( $params ) )
                $customConds = ' AND ( ' . eZContentLanguage::languagesSQLFilter( 'eztags' ) . ' ) ';
            else
                $customConds = ' WHERE ( ' . eZContentLanguage::languagesSQLFilter( 'eztags' ) . ' ) ';
        }

        $customFields = array( array( 'operation' => 'COUNT( * )', 'name' => 'row_count' ) );

        $rows = eZPersistentObject::fetchObjectList( self::definition(), array(), $params,
                                                     array(), null, false, false,
                                                     $customFields, null, $customConds );
        return $rows[0]['row_count'];
    }

    /**
     * Returns array of eZTagsObject objects for given parent ID
     *
     * @static
     * @param integer $parentID
     * @return array
     */
    static function fetchByParentID( $parentID, $mainTranslation = false )
    {
        return self::fetchList( array( 'parent_id' => $parentID, 'main_tag_id' => 0 ), null, true, null, $mainTranslation );
    }

    /**
     * Returns count of eZTagsObject objects for given parent ID
     *
     * @static
     * @param integer $parentID
     * @return integer
     */
    static function childrenCountByParentID( $parentID, $mainTranslation = false )
    {
        return self::fetchListCount( array( 'parent_id' => $parentID, 'main_tag_id' => 0 ), $mainTranslation );
    }

    /**
     * Returns array of eZTagsObject objects that are synonyms of provided tag ID
     *
     * @static
     * @param integer $mainTagID
     * @return array
     */
    static function fetchSynonyms( $mainTagID, $mainTranslation = false )
    {
        return self::fetchList( array( 'main_tag_id' => $mainTagID ), null, true, null, $mainTranslation );
    }

    /**
     * Returns count of eZTagsObject objects that are synonyms of provided tag ID
     *
     * @static
     * @param integer $mainTagID
     * @return integer
     */
    static function synonymsCount( $mainTagID, $mainTranslation = false )
    {
        return self::fetchListCount( array( 'main_tag_id' => $mainTagID ), $mainTranslation );
    }

    /**
     * Returns array of eZTagsObject objects for given keyword
     *
     * @static
     * @param mixed $keyword
     * @return array
     */
    static function fetchByKeyword( $keyword, $mainTranslation = false )
    {
        return self::fetchList( array( 'keyword' => $keyword ), null, true, null, $mainTranslation );
    }

    /**
     * Returns the array of eZTagsObject objects for given path string
     *
     * @static
     * @param string $pathString
     * @return array
     */
    static function fetchByPathString( $pathString, $mainTranslation = false )
    {
        return self::fetchList( array( 'path_string' => array( 'like', $pathString . '%' ),
                                       'main_tag_id' => 0 ), null, true, null, $mainTranslation );
    }

    function recursivelyDeleteTag()
    {
        foreach ( $this->getChildren( true ) as $child )
        {
            $child->recursivelyDeleteTag();
        }

        $this->registerSearchObjects();

        foreach ( $this->getSynonyms( true ) as $synonym )
        {
            $synonym->remove();
        }

        $this->remove();
    }

    function moveChildrenBelowAnotherTag( $targetTag )
    {
        if ( !$targetTag instanceof eZTagsObject )
            return;

        $currentTime = time();
        $children = $this->getChildren( true );
        foreach ( $children as $child )
        {
            $childSynonyms = $child->getSynonyms( true );
            foreach ( $childSynonyms as $childSynonym )
            {
                $childSynonym->setAttribute( 'parent_id', $targetTag->attribute( 'id' ) );
                $childSynonym->store();
            }

            $child->setAttribute( 'parent_id', $targetTag->attribute( 'id' ) );
            $child->setAttribute( 'modified', $currentTime );
            $child->store();
            $child->updatePathString( $targetTag );
            $child->updateDepth( $targetTag );
        }
    }

    function transferObjectsToAnotherTag( $destination )
    {
        if ( !$destination instanceof eZTagsObject )
        {
            $destination = eZTagsObject::fetchWithMainTranslation( (int) $destination );
            if ( !$destination instanceof eZTagsObject )
                return;
        }

        foreach ( $this->getTagAttributeLinks() as $tagAttributeLink )
        {
            $link = eZTagsAttributeLinkObject::fetchByObjectAttributeAndKeywordID(
                        $tagAttributeLink->attribute( 'objectattribute_id' ),
                        $tagAttributeLink->attribute( 'objectattribute_version' ),
                        $tagAttributeLink->attribute( 'object_id' ),
                        $destination->attribute( 'id' ) );

            if ( !$link instanceof eZTagsAttributeLinkObject )
            {
                $tagAttributeLink->setAttribute( 'keyword_id', $destination->attribute( 'id' ) );
                $tagAttributeLink->store();
            }
            else
            {
                $tagAttributeLink->remove();
            }
        }
    }

    function remove( $conditions = null, $extraConditions = null )
    {
        foreach ( $this->getTagAttributeLinks() as $tagAttributeLink )
        {
            $tagAttributeLink->remove();
        }

        foreach ( $this->getTranslations() as $translation )
        {
            $translation->remove();
        }

        parent::remove( $conditions, $extraConditions );
    }

    /**
     * Returns if tag with provided keyword and parent ID already exists, not counting tag with provided tag ID
     *
     * @static
     * @param string $keyword
     * @param integer $parentID
     * @return bool
     */
    static function exists( $tagID, $keyword, $parentID )
    {
        $params = array( 'keyword' => array( 'like', trim( $keyword ) ), 'parent_id' => $parentID );

        if ( $tagID > 0 )
        {
            $params['id'] = array( '!=', $tagID );
        }

        $count = self::fetchListCount( $params );
        if ( $count > 0 )
            return true;
        return false;
    }

    /**
     * Fetches subtree of tags by specified parameters
     *
     * @static
     * @param array $params
     * @param integer $tagID
     * @return array
     */
    static function subTreeByTagID( $params = array(), $tagID = 0 )
    {
        if ( !is_numeric( $tagID ) || (int) $tagID < 0 )
            return false;

        $tag = eZTagsObject::fetch( (int) $tagID );
        if ( (int) $tagID > 0 && !$tag instanceof eZTagsObject && $tag->attribute( 'main_tag_id' ) != 0 )
            return false;

        if ( !is_array( $params ) )
            $params = array();

        $offset          = ( isset( $params['Offset'] ) && (int) $params['Offset'] > 0 )   ? (int) $params['Offset']           : 0;
        $limit           = ( isset( $params['Limit'] ) && (int) $params['Limit'] > 0 )     ? (int) $params['Limit']            : 0;
        $sortBy          = ( isset( $params['SortBy'] ) && is_array( $params['SortBy'] ) ) ? $params['SortBy']                 : array();
        $depth           = ( isset( $params['Depth'] ) )                                   ? $params['Depth']                  : false;
        $depthOperator   = ( isset( $params['DepthOperator'] ) )                           ? $params['DepthOperator']          : false;
        $includeSynonyms = ( isset( $params['IncludeSynonyms'] ) )                         ? (bool) $params['IncludeSynonyms'] : false;

        $fetchParams = array();

        if ( (int) $tagID > 0 )
        {
            $fetchParams['path_string'] = array( 'like', '%/' . (string) ( (int) $tagID ) . '/%' );
            $fetchParams['id'] = array( '!=', (int) $tagID );
        }

        if ( !$includeSynonyms )
            $fetchParams['main_tag_id'] = 0;

        if ( $depth !== false && (int) $depth > 0 )
        {
            $tagDepth = 0;
            if ( $tag instanceof eZTagsObject )
                $tagDepth = (int) $tag->attribute( 'depth' );

            $depth = (int) $depth + $tagDepth;

            $sqlDepthOperator = '<=';
            if ( $depthOperator == 'lt' )
                $sqlDepthOperator = '<';
            else if ( $depthOperator == 'gt' )
                $sqlDepthOperator = '>';
            else if ( $depthOperator == 'le' )
                $sqlDepthOperator = '<=';
            else if ( $depthOperator == 'ge' )
                $sqlDepthOperator = '>=';
            else if ( $depthOperator == 'eq' )
                $sqlDepthOperator = '=';

            $fetchParams['depth'] = array( $sqlDepthOperator, $depth );
        }

        $limits = null;
        if ( $limit > 0 )
        {
            $limits = array(
                'offset' => $offset,
                'limit' => $limit,
            );
        }

        $sorts = array();
        if ( !empty( $sortBy ) )
        {
            $columnArray = array( 'id', 'parent_id', 'main_tag_id', 'keyword', 'depth', 'path_string', 'modified' );
            $orderArray = array( 'asc', 'desc' );

            if ( count( $sortBy ) == 2 && !is_array( $sortBy[0] ) && !is_array( $sortBy[1] ) )
            {
                $sortBy = array( $sortBy );
            }

            foreach ( $sortBy as $sortCond )
            {
                if ( is_array( $sortCond ) && count( $sortCond ) == 2 )
                {
                    if ( in_array( strtolower( trim( $sortCond[0] ) ), $columnArray ) )
                    {
                        $sortCond[0] = trim( strtolower( $sortCond[0] ) );

                        if( in_array( strtolower( trim( $sortCond[1] ) ), $orderArray ) )
                            $sortCond[1] = trim( strtolower( $sortCond[1] ) );
                        else
                            $sortCond[1] = 'asc';

                        if ( !array_key_exists( $sortCond[0], $sorts ) )
                            $sorts[$sortCond[0]] = $sortCond[1];
                    }
                }
            }
        }

        if ( empty( $sorts ) )
            $sorts = null;

        $fetchResults = self::fetchList( $fetchParams, $limits, true, $sorts );

        if ( is_array( $fetchResults ) && !empty( $fetchResults ) )
            return $fetchResults;

        return false;
    }

    /**
     * Fetches subtree tag count by specified parameters
     *
     * @static
     * @param array $params
     * @param integer $tagID
     * @return integer
     */
    static function subTreeCountByTagID( $params = array(), $tagID = 0 )
    {
        if ( !is_numeric( $tagID ) || (int) $tagID < 0 )
            return 0;

        $tag = eZTagsObject::fetch( (int) $tagID );
        if ( (int) $tagID > 0 && !$tag instanceof eZTagsObject && $tag->attribute( 'main_tag_id' ) != 0 )
            return 0;

        if ( !is_array( $params ) )
            $params = array();

        $depth           = ( isset( $params['Depth'] ) )                                   ? $params['Depth']                  : false;
        $depthOperator   = ( isset( $params['DepthOperator'] ) )                           ? $params['DepthOperator']          : false;
        $includeSynonyms = ( isset( $params['IncludeSynonyms'] ) )                         ? (bool) $params['IncludeSynonyms'] : false;

        $fetchParams = array();

        if ( (int) $tagID > 0 )
        {
            $fetchParams['path_string'] = array( 'like', '%/' . (string) ( (int) $tagID ) . '/%' );
            $fetchParams['id'] = array( '!=', (int) $tagID );
        }

        if ( !$includeSynonyms )
            $fetchParams['main_tag_id'] = 0;

        if ( $depth !== false && (int) $depth > 0 )
        {
            $tagDepth = 0;
            if ( $tag instanceof eZTagsObject )
                $tagDepth = (int) $tag->attribute( 'depth' );

            $depth = (int) $depth + $tagDepth;

            $sqlDepthOperator = '<=';
            if ( $depthOperator == 'lt' )
                $sqlDepthOperator = '<';
            else if ( $depthOperator == 'gt' )
                $sqlDepthOperator = '>';
            else if ( $depthOperator == 'le' )
                $sqlDepthOperator = '<=';
            else if ( $depthOperator == 'ge' )
                $sqlDepthOperator = '>=';
            else if ( $depthOperator == 'eq' )
                $sqlDepthOperator = '=';

            $fetchParams['depth'] = array( $sqlDepthOperator, $depth );
        }

        $count = self::fetchListCount( $fetchParams );

        if ( is_numeric( $count ) )
            return $count;

        return 0;
    }

    static function generateModuleResultPath( $tag = false, $view = false, $attribute = false, $textPart = false )
    {
        $moduleResultPath = array();

        $generateUrls = false;
        if ( is_string( $view ) && is_string( $attribute ) )
            $generateUrls = true;

        if ( is_string( $textPart ) )
        {
            $moduleResultPath[] = array( 'text'   => $textPart,
                                         'url'    => false );
        }

        if ( $tag instanceof eZTagsObject )
        {
            $moduleResultPath[] = array( 'tag_id' => $tag->attribute( 'id' ),
                                         'text'   => $tag->attribute( 'keyword' ),
                                         'url'    => false );

            $tempTag = $tag;
            while ( $tempTag->hasParent( true ) )
            {
                $tempTag = $tempTag->getParent( true );
                $moduleResultPath[] = array( 'tag_id' => $tempTag->attribute( 'id' ),
                                             'text'   => $tempTag->attribute( 'keyword' ),
                                             'url'    => $generateUrls ? 'tags/' . $view . '/' . $tempTag->attribute( $attribute ) : false );
            }
        }

        return array_reverse( $moduleResultPath );
    }

    function getMainTranslation()
    {
        return eZTagsKeyword::fetch( $this->attribute( 'id' ), $this->attribute( 'main_language_id' ) );
    }

    function getTranslations()
    {
        return eZTagsKeyword::fetchByTagID( $this->attribute( 'id' ) );
    }

    function getTranslationsCount()
    {
        return eZTagsKeyword::fetchCountByTagID( $this->attribute( 'id' ) );
    }

    function translationByLanguageID( $languageID )
    {
        return eZTagsKeyword::fetch( $this->attribute( 'id' ), (int) $languageID );
    }

    function translationByLocale( $locale )
    {
        return eZTagsKeyword::fetchByLocale( $this->attribute( 'id' ), $locale );
    }

    function getKeyword()
    {
        if ( $this->attribute( 'id' ) == null )
            return $this->Keyword;

        $translation = $this->translationByLocale( $this->CurrentLanguage );
        if ( $translation instanceof eZTagsKeyword )
            return $translation->attribute( 'keyword' );

        return '';
    }

    function languageNameArray()
    {
        $languageNameArray = array();
        $translations = $this->getTranslations();

        foreach ( $translations as $translation )
        {
            $languageName = $translation->languageName();

            if ( is_array( $languageName ) )
                $languageNameArray[$languageName['locale']] = $languageName['name'];
        }

        return $languageNameArray;
    }

    function getCurrentLanguage()
    {
        return $this->CurrentLanguage;
    }

    function getAvailableLanguages()
    {
        $languages = eZContentLanguage::decodeLanguageMask( $this->attribute( 'language_mask' ), true );
        return $languages['language_list'];
    }

    function updateMainTranslation( $languageID, $forceStore = false )
    {
        $trans = $this->translationByLanguageID( $languageID );
        if ( $trans instanceof eZTagsKeyword )
        {
            $this->setAttribute( 'main_language_id', $trans->attribute( 'language_id' ) );

            if ( $forceStore )
                $this->store();

            return true;
        }

        return false;
    }

    function updateLanguageMask( $mask = false, $forceStore = false )
    {
        if ( $mask == false )
        {
            $translationList = $this->getTranslations();

            $locales = array();
            foreach ( $translationList as $translation )
            {
                $locales[] = $translation->Locale;
            }

            $mask = eZContentLanguage::maskByLocale( $locales );
        }

        $this->setAttribute( 'language_mask', $mask );

        if ( $forceStore )
            $this->store();
    }

    function isAlwaysAvailable()
    {
        $zerothBit = (int) $this->attribute( 'language_mask' ) & 1;
        return $zerothBit > 0 ? true : false;
    }

    function setAlwaysAvailable( $alwaysAvailable, $forceStore = false )
    {
        $languageMask = (int) $this->attribute( 'language_mask' ) & ~1;
        $zerothBit = $alwaysAvailable ? 1 : 0;

        $this->setAttribute( 'language_mask', $languageMask | $zerothBit );

        if ( $forceStore )
            $this->store();
    }

    private function fetchTranslated( $fetchMainTranslation = false, $locale = false, $forceLoad = false )
    {
        if ( $this->attribute( 'language_mask' ) > 0 )
        {
            $translation = false;

            if ( $fetchMainTranslation )
            {
                $translation = $this->getMainTranslation();
            }
            else if ( $locale !== false )
            {
                $translation = $this->translationByLocale( $locale );
                if ( !$translation instanceof eZTagsKeyword && $forceLoad )
                {
                    if ( eZContentLanguage::fetchByLocale( $locale ) instanceof eZContentLanguage )
                    {
                        $this->CurrentLanguage = $locale;
                        return $this;
                    }

                    return false;
                }
            }
            else
            {
                $language = eZContentLanguage::topPriorityLanguageByMask( $this->attribute( 'language_mask' ) );
                if ( $language instanceof eZContentLanguage )
                    $translation = $this->translationByLocale( $language->attribute( 'locale' ) );

                if ( !$translation instanceof eZTagsKeyword &&
                     ( $this->isAlwaysAvailable() || eZINI::instance()->variable( 'RegionalSettings', 'ShowUntranslatedObjects' ) == 'enabled' ) )
                    $translation = $this->getMainTranslation();
            }

            if ( $translation instanceof eZTagsKeyword )
            {
                $this->CurrentLanguage = $translation->attribute( 'locale' );
                return $this;
            }
        }

        return false;
    }

    private static function processTagsForTranslations( $tags, $fetchMainTranslation = false, $locale = false )
    {
        if ( !is_array( $tags ) )
            return array();

        $returnArray = array();

        foreach ( $tags as $tag )
        {
            if ( $tag instanceof eZTagsObject )
            {
                $translatedTag = $tag->fetchTranslated( $fetchMainTranslation, $locale );
                if ( $translatedTag instanceof eZTagsObject )
                    $returnArray[] = $translatedTag;
            }
        }

        return $returnArray;
    }

    /**
     * Recursively deletes all children tags of the given tag, including the given tag itself
     * Deprecated: see $this->recursivelyDeleteTag()
     *
     * @static
     * @deprecated
     * @param eZTagsObject $rootTag
     */
    static function recursiveTagDelete( $rootTag )
    {
        if ( !$rootTag instanceof eZTagsObject )
            return;

        $rootTag->recursivelyDeleteTag();
    }

    /**
     * Moves all children tags of the provided tag to the new location
     * Deprecated: see $this->moveChildrenBelowAnotherTag()
     *
     * @static
     * @deprecated
     * @param eZTagsObject $tag
     * @param eZTagsObject $targetTag
     */
    static function moveChildren( $tag, $targetTag )
    {
        if ( !$tag instanceof eZTagsObject )
            return;

        $tag->moveChildrenBelowAnotherTag( $targetTag );
    }

    private $CurrentLanguage = false;
}

?>
