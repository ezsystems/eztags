<?php

/**
 * eZTagsObject class inherits eZPersistentObject class
 * to be able to access eztags database table through API
 *
 * @property string $Keyword
 */
class eZTagsObject extends eZPersistentObject
{
    /**
     * Constructor
     *
     * @param array $row
     * @param string|bool $locale
     */
    function __construct( $row, $locale = false )
    {
        if ( !isset( $row['remote_id'] ) || !$row['remote_id'] )
            $row['remote_id'] = self::generateRemoteID();

        parent::eZPersistentObject( $row );

        if ( isset( $row['locale'] ) && $row['locale'] != null )
            $this->CurrentLanguage = $row['locale'];
        else
            $this->CurrentLanguage = $locale;
    }

    /**
     * Returns the definition array for eZTagsObject
     *
     * @return array
     */
    static public function definition()
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
                                                      'remote_id'        => array( 'name'     => 'RemoteID',
                                                                                   'datatype' => 'string',
                                                                                   'default'  => '',
                                                                                   'required' => true ),
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
                                                      'related_objects_count'     => 'getRelatedObjectsCount',
                                                      'subtree_limitations'       => 'getSubTreeLimitations',
                                                      'subtree_limitations_count' => 'getSubTreeLimitationsCount',
                                                      'main_tag'                  => 'getMainTag',
                                                      'synonyms'                  => 'getSynonyms',
                                                      'synonyms_count'            => 'getSynonymsCount',
                                                      'is_synonym'                => 'isSynonym',
                                                      'icon'                      => 'getIcon',
                                                      'url'                       => 'getUrl',
                                                      'clean_url'                 => 'getCleanUrl',
                                                      'path'                      => 'getPath',
                                                      'path_count'                => 'getPathCount',
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
                      'name'                => 'eztags' );
    }

    /**
     * Updates path string of the tag and all of it's children and synonyms.
     */
    public function updatePathString()
    {
        $parentTag = $this->getParent( true );
        $pathStringPrefix = $parentTag instanceof self ? $parentTag->attribute( 'path_string' ) : '/';

        $this->setAttribute( 'path_string', $pathStringPrefix . $this->attribute( 'id' ) . '/' );
        $this->store();

        foreach ( $this->getSynonyms( true ) as $s )
        {
            $s->setAttribute( 'path_string', $pathStringPrefix . $s->attribute( 'id' ) . '/' );
            $s->store();
        }

        foreach ( $this->getChildren( 0, null, true ) as $c )
        {
            $c->updatePathString();
        }
    }

    /**
     * Updates depth of the tag and all of it's children and synonyms.
     */
    public function updateDepth()
    {
        $parentTag = $this->getParent( true );
        $depth = $parentTag instanceof self ? $parentTag->attribute( 'depth' ) + 1 : 1;

        $this->setAttribute( 'depth', $depth );
        $this->store();

        foreach ( $this->getSynonyms( true ) as $s )
        {
            $s->setAttribute( 'depth', $depth );
            $s->store();
        }

        foreach ( $this->getChildren( 0, null, true ) as $c )
        {
            $c->updateDepth();
        }
    }

    /**
     * Returns whether tag has a parent
     *
     * @param bool $mainTranslation
     *
     * @return bool
     */
    public function hasParent( $mainTranslation = false )
    {
        return $this->getParent( $mainTranslation ) instanceof self;
    }

    /**
     * Returns tag parent
     *
     * @param bool $mainTranslation
     *
     * @return eZTagsObject
     */
    public function getParent( $mainTranslation = false )
    {
        if ( $mainTranslation )
            return self::fetchWithMainTranslation( $this->attribute( 'parent_id' ) );

        return self::fetch( $this->attribute( 'parent_id' ) );
    }

    /**
     * Returns first level children tags
     *
     * @param bool $mainTranslation
     * @param int $offset
     * @param int $limit
     *
     * @return eZTagsObject[]
     */
    public function getChildren( $offset = 0, $limit = null, $mainTranslation = false )
    {
        return self::fetchByParentID( $this->attribute( 'id' ), $offset, $limit, $mainTranslation );
    }

    /**
     * Returns count of first level children tags
     *
     * @param bool $mainTranslation
     *
     * @return int
     */
    public function getChildrenCount( $mainTranslation = false )
    {
        return self::childrenCountByParentID( $this->attribute( 'id' ), $mainTranslation );
    }

    /**
     * Returns objects related to this tag
     *
     * @return eZContentObject[]
     */
    public function getRelatedObjects()
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

        if ( !is_array( $result ) || empty( $result ) )
            return array();

        $objectIDArray = array();
        foreach ( $result as $r )
        {
            $objectIDArray[] = $r['object_id'];
        }

        return eZContentObject::fetchIDArray( $objectIDArray );
    }

    /**
     * Returns the count of objects related to this tag
     *
     * @return int
     */
    public function getRelatedObjectsCount()
    {
        // Not an easy task to fetch published objects with API and take care of current_version, status
        // and attribute version, so just use SQL to fetch the object count in one go
        $tagID = (int) $this->attribute( 'id' );

        $db = eZDB::instance();
        $result = $db->arrayQuery( "SELECT COUNT(DISTINCT o.id) AS count FROM eztags_attribute_link l
                                   INNER JOIN ezcontentobject o ON l.object_id = o.id
                                   AND l.objectattribute_version = o.current_version
                                   AND o.status = " . eZContentObject::STATUS_PUBLISHED . "
                                   WHERE l.keyword_id = $tagID" );

        if ( is_array( $result ) && !empty( $result ) )
            return (int) $result[0]['count'];

        return 0;
    }

    /**
     * Returns list of eZContentClassAttribute objects (represented as subtree limitations)
     *
     * @return eZContentClassAttribute[]
     */
    public function getSubTreeLimitations()
    {
        if ( $this->attribute( 'main_tag_id' ) != 0 )
            return array();

        return parent::fetchObjectList( eZContentClassAttribute::definition(), null,
                                                    array( 'data_type_string'              => 'eztags',
                                                           eZTagsType::SUBTREE_LIMIT_FIELD => $this->attribute( 'id' ),
                                                           'version'                       => eZContentClass::VERSION_STATUS_DEFINED ) );
    }

    /**
     * Returns count of eZContentClassAttribute objects (represented as subtree limitation count)
     *
     * @return int
     */
    public function getSubTreeLimitationsCount()
    {
        if ( $this->attribute( 'main_tag_id' ) != 0 )
            return 0;

        return parent::count( eZContentClassAttribute::definition(),
                                          array( 'data_type_string'              => 'eztags',
                                                 eZTagsType::SUBTREE_LIMIT_FIELD => $this->attribute( 'id' ),
                                                 'version'                       => eZContentClass::VERSION_STATUS_DEFINED ) );
    }

    /**
     * Checks if any of the parents have subtree limits defined
     *
     * @return bool
     */
    public function isInsideSubTreeLimit()
    {
        /** @var eZTagsObject[] $path */
        $path = $this->getPath( true, true );

        if ( is_array( $path ) && !empty( $path ) )
        {
            foreach ( $path as $tag )
            {
                if ( $tag->getSubTreeLimitationsCount() > 0 )
                    return true;
            }
        }

        return false;
    }

    /**
     * Returns the main tag for synonym
     *
     * @param bool $mainTranslation
     *
     * @return eZTagsObject
     */
    public function getMainTag( $mainTranslation = false )
    {
        if ( $mainTranslation )
            return self::fetchWithMainTranslation( $this->attribute( 'main_tag_id' ) );

        return self::fetch( $this->attribute( 'main_tag_id' ) );
    }

    /**
     * Returns synonyms for the tag
     *
     * @param bool $mainTranslation
     *
     * @return eZTagsObject[]
     */
    public function getSynonyms( $mainTranslation = false )
    {
        return self::fetchSynonyms( $this->attribute( 'id' ), $mainTranslation );
    }

    /**
     * Returns synonym count for the tag
     *
     * @param bool $mainTranslation
     *
     * @return int
     */
    public function getSynonymsCount( $mainTranslation = false )
    {
        return self::synonymsCount( $this->attribute( 'id' ), $mainTranslation );
    }

    /**
     * Tells whether tag object is a synonym of another tag object
     *
     * @return bool
     */
    public function isSynonym()
    {
        return $this->attribute( 'main_tag_id' ) > 0;
    }

    /**
     * Returns all links to objects for this tag
     *
     * @return eZTagsAttributeLinkObject[]
     */
    public function getTagAttributeLinks()
    {
        return eZTagsAttributeLinkObject::fetchByTagID( $this->attribute( 'id' ) );
    }

    /**
     * Returns icon associated with the tag, while respecting hierarchy structure
     *
     * @return string
     */
    public function getIcon()
    {
        $ini = eZINI::instance( 'eztags.ini' );
        /** @var array $iconMap */
        $iconMap = $ini->variable( 'Icons', 'IconMap' );
        $defaultIcon = $ini->variable( 'Icons', 'Default' );

        if ( $this->attribute( 'main_tag_id' ) > 0 )
            $tag = $this->getMainTag( true );
        else
            $tag = $this;

        $tagID = $tag->attribute( 'id' );
        if ( array_key_exists( $tagID, $iconMap ) && !empty( $iconMap[$tagID] ) )
            return $iconMap[$tagID];

        /** @var eZTagsObject[] $path */
        $path = $tag->getPath( true, true );
        if ( is_array( $path ) && !empty( $path ) )
        {
            foreach ( $path as $pathElement )
            {
                $pathElementID = $pathElement->attribute( 'id' );
                if ( array_key_exists( $pathElementID, $iconMap ) && !empty( $iconMap[$pathElementID] ) )
                    return $iconMap[$pathElementID];
            }
        }

        return $defaultIcon;
    }

    /**
     * Returns the URL of the tag, to be used in tags/view
     *
     * @param bool $clean
     *
     * @return string
     */
    public function getUrl( $clean = false )
    {
        /** @var eZTagsObject[] $path */
        $path = $this->getPath();
        $fullPathCount = $this->getPathCount( true );
        $urlPrefix = trim( eZINI::instance( 'eztags.ini' )->variable( 'GeneralSettings', 'URLPrefix' ) );
        $urlPrefix = trim( $urlPrefix, '/' );

        $keywordArray = array();

        if ( is_array( $path ) )
        {
            if ( count( $path ) != $fullPathCount )
            {
                return 'tags/id/' . $this->attribute( 'id' );
            }
            else
            {
                foreach ( $path as $tag )
                {
                    $keywordArray[] = $clean ? $tag->attribute( 'keyword' ) : urlencode( $tag->attribute( 'keyword' ) );
                }

                $keywordArray[] = $clean ? $this->attribute( 'keyword' ) : urlencode( $this->attribute( 'keyword' ) );

                return $clean ? implode( '/', $keywordArray ) : $urlPrefix . '/' . implode( '/', $keywordArray );
            }
        }

        return $clean ? $this->attribute( 'keyword' ) : $urlPrefix . '/' . urlencode( $this->attribute( 'keyword' ) );
    }

    /**
     * Returns the clean URL of the tag, without prefix and without URL encoding
     *
     * @return string
     */
    public function getCleanUrl()
    {
        return $this->getUrl( true );
    }

    /**
     * Returns the array of eZTagsObject objects which are parents of this tag
     *
     * @param bool $reverseSort
     * @param bool $mainTranslation
     *
     * @return eZTagsObject[]
     */
    public function getPath( $reverseSort = false, $mainTranslation = false )
    {
        $pathArray = explode( '/', trim( $this->attribute( 'path_string' ), '/' ) );

        if ( !is_array( $pathArray ) || empty( $pathArray ) || count( $pathArray ) == 1 )
            return array();

        $pathArray = array_slice( $pathArray, 0, count( $pathArray ) - 1 );

        return self::fetchList( array( 'id' => array( $pathArray ) ),
                                null,
                                array( 'path_string' => $reverseSort != false ? 'desc' : 'asc' ),
                                $mainTranslation );
    }

    /**
     * Returns the count of eZTagsObject objects which are parents of this tag
     *
     * @param bool $mainTranslation
     *
     * @return int
     */
    public function getPathCount( $mainTranslation = false )
    {
        $pathArray = explode( '/', trim( $this->attribute( 'path_string' ), '/' ) );

        if ( !is_array( $pathArray ) || empty( $pathArray ) || count( $pathArray ) == 1 )
            return 0;

        $pathArray = array_slice( $pathArray, 0, count( $pathArray ) - 1 );

        return self::fetchListCount( array( 'id' => array( $pathArray ) ), $mainTranslation );
    }

    /**
     * Returns the parent string of the tag
     *
     * @return string
     */
    public function getParentString()
    {
        $keywordsArray = array();

        /** @var eZTagsObject[] $path */
        $path = $this->getPath( false, true );
        if ( is_array( $path ) && !empty( $path ) )
        {
            foreach ( $path as $tag )
            {
                $synonymsCount = $tag->getSynonymsCount( true );
                $keywordsArray[] = $synonymsCount > 0 ? $tag->attribute( 'keyword' ) . ' (+' . $synonymsCount . ')' : $tag->attribute( 'keyword' );
            }
        }

        $synonymsCount = $this->getSynonymsCount( true );
        $keywordsArray[] = $synonymsCount > 0 ? $this->attribute( 'keyword' ) . ' (+' . $synonymsCount . ')' : $this->attribute( 'keyword' );

        return implode( ' / ', $keywordsArray );
    }

    /**
     * Updates modified timestamp on current tag and all of its parents
     * Expensive to run through API, so SQL takes care of it
     */
    public function updateModified()
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
     */
    public function registerSearchObjects()
    {
        $eZTagsINI = eZINI::instance( 'eztags.ini' );

        if ( eZINI::instance( 'site.ini' )->variable( 'SearchSettings', 'DelayedIndexing' ) !== 'disabled'
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
     *
     * @param int $id
     * @param mixed $locale
     *
     * @return eZTagsObject
     */
    static public function fetch( $id, $locale = false )
    {
        if ( is_string( $locale ) )
            $tags = self::fetchList( array( 'id' => $id ), null, null, false, $locale );
        else
            $tags = self::fetchList( array( 'id' => $id ) );

        if ( is_array( $tags ) && !empty( $tags ) )
            return $tags[0];

        return false;
    }

    /**
     * Returns eZTagsObject for given ID, using the main translation of the tag
     *
     * @static
     *
     * @param int $id
     *
     * @return eZTagsObject
     */
    static public function fetchWithMainTranslation( $id )
    {
        $tags = self::fetchList( array( 'id' => $id ), null, null, true );

        if ( is_array( $tags ) && !empty( $tags ) )
            return $tags[0];

        return false;
    }

    /**
     * Returns array of eZTagsObject objects for given params
     *
     * @static
     *
     * @param array $params
     * @param array $limits
     * @param array $sorts
     * @param bool $mainTranslation
     * @param mixed $locale
     *
     * @return eZTagsObject[]
     */
    static public function fetchList( $params, $limits = null, $sorts = null, $mainTranslation = false, $locale = false )
    {
        $customConds = self::fetchCustomCondsSQL( $params, $mainTranslation, $locale );

        if ( is_array( $params ) )
        {
            $newParams = array();
            foreach ( $params as $key => $value )
            {
                if ( $key != 'keyword' )
                    $newParams[$key] = $value;
                else
                    $newParams['eztags_keyword.keyword'] = $value;
            }

            $params = $newParams;
        }

        if ( is_array( $sorts ) )
        {
            $newSorts = array();
            foreach ( $sorts as $key => $value )
            {
                if ( $key != 'keyword' )
                    $newSorts[$key] = $value;
                else
                    $newSorts['eztags_keyword.keyword'] = $value;
            }

            $sorts = $newSorts;
        }
        else if ( $sorts == null )
        {
            $sorts = array( 'eztags_keyword.keyword' => 'asc' );
        }

        $tagsList = parent::fetchObjectList( self::definition(), array(), $params,
                                                         $sorts, $limits, true, false,
                                                         array( 'DISTINCT eztags.*',
                                                                array( 'operation' => 'eztags_keyword.keyword',
                                                                       'name'      => 'keyword' ),
                                                                array( 'operation' => 'eztags_keyword.locale',
                                                                       'name'      => 'locale' ) ),
                                                         array( 'eztags_keyword' ), $customConds );
        return $tagsList;
    }

    /**
     * Returns count of eZTagsObject objects for given params
     *
     * @static
     *
     * @param mixed $params
     * @param bool $mainTranslation
     * @param mixed $locale
     *
     * @return int
     */
    static public function fetchListCount( $params, $mainTranslation = false, $locale = false )
    {
        $customConds = self::fetchCustomCondsSQL( $params, $mainTranslation, $locale );

        if ( is_array( $params ) )
        {
            $newParams = array();
            foreach ( $params as $key => $value )
            {
                if ( $key != 'keyword' )
                    $newParams[$key] = $value;
                else
                    $newParams['eztags_keyword.keyword'] = $value;
            }

            $params = $newParams;
        }

        $tagsList = parent::fetchObjectList( self::definition(), array(), $params,
                                                         array(), null, false, false,
                                                         array( array( 'operation' => 'COUNT( * )',
                                                                       'name'      => 'row_count' ) ),
                                                         array( 'eztags_keyword' ), $customConds );

        return $tagsList[0]['row_count'];
    }

    /**
     * Returns the SQL for custom fetching of tags with eZPersistentObject
     *
     * @static
     *
     * @param mixed $params
     * @param bool $mainTranslation
     * @param mixed $locale
     *
     * @return string
     */
    static public function fetchCustomCondsSQL( $params, $mainTranslation = false, $locale = false )
    {
        $customConds = is_array( $params ) && !empty( $params ) ? " AND " : " WHERE ";
        $customConds .= " eztags.id = eztags_keyword.keyword_id ";

        if ( $mainTranslation !== false )
        {
            $customConds .= " AND eztags.main_language_id + MOD( eztags.language_mask, 2 ) = eztags_keyword.language_id ";
        }
        else if ( is_string( $locale ) )
        {
            $db = eZDB::instance();
            $customConds .= " AND " . eZContentLanguage::languagesSQLFilter( 'eztags' ) . " ";
            $customConds .= " AND eztags_keyword.locale = '" . $db->escapeString( $locale ) . "' ";
        }
        else
        {
            $customConds .= " AND " . eZContentLanguage::languagesSQLFilter( 'eztags' ) . " ";
            $customConds .= " AND " . eZContentLanguage::sqlFilter( 'eztags_keyword', 'eztags' ) . " ";
        }

        return $customConds;
    }

    /**
     * Returns the list of limitations that eZ Tags support
     *
     * @static
     *
     * @return array
     */
    static public function fetchLimitations()
    {
        /** @var eZTagsObject[] $tags */
        $tags = self::fetchList( array( 'parent_id' => 0, 'main_tag_id' => 0 ), null, null, true );

        if ( !is_array( $tags ) )
            return array();

        $returnArray = array();
        foreach ( $tags as $tag )
        {
            $returnArray[] = array( 'name' => $tag->attribute( 'keyword' ), 'id' => $tag->attribute( 'id' ) );
        }

        return $returnArray;
    }

    /**
     * Backwards compatible remote ID generator
     *
     * @static
     *
     * @return string
     */
    static public function generateRemoteID()
    {
        // eZRemoteIdUtility introduced in eZ Publish version 4.5
        if ( method_exists( 'eZRemoteIdUtility', 'generate' ) )
            return eZRemoteIdUtility::generate( 'tag' );

        return md5( (string) mt_rand() . (string) time() );
    }

    /**
     * Returns array of eZTagsObject objects for given parent ID
     *
     * @static
     *
     * @param int $parentID
     * @param bool $mainTranslation
     * @param int $offset
     * @param int $limit
     *
     * @return eZTagsObject[]
     */
    static public function fetchByParentID( $parentID, $offset = 0, $limit = null, $mainTranslation = false )
    {
        if ( $offset === 0 && $limit === null )
        {
            $limits = null;
        }
        else
        {
            $limits = array( 'offset' => $offset, 'limit' => $limit );
        }
        return self::fetchList(
            array( 'parent_id' => $parentID, 'main_tag_id' => 0 ),
            $limits,
            null,
            $mainTranslation
        );
    }

    /**
     * Returns count of eZTagsObject objects for given parent ID
     *
     * @static
     *
     * @param int $parentID
     * @param bool $mainTranslation
     *
     * @return int
     */
    static public function childrenCountByParentID( $parentID, $mainTranslation = false )
    {
        return self::fetchListCount( array( 'parent_id' => $parentID, 'main_tag_id' => 0 ), $mainTranslation );
    }

    /**
     * Returns array of eZTagsObject objects that are synonyms of provided tag ID
     *
     * @static
     *
     * @param int $mainTagID
     * @param bool $mainTranslation
     *
     * @return eZTagsObject[]
     */
    static public function fetchSynonyms( $mainTagID, $mainTranslation = false )
    {
        return self::fetchList( array( 'main_tag_id' => $mainTagID ), null, null, $mainTranslation );
    }

    /**
     * Returns count of eZTagsObject objects that are synonyms of provided tag ID
     *
     * @static
     *
     * @param int $mainTagID
     * @param bool $mainTranslation
     *
     * @return int
     */
    static public function synonymsCount( $mainTagID, $mainTranslation = false )
    {
        return self::fetchListCount( array( 'main_tag_id' => $mainTagID ), $mainTranslation );
    }

    /**
     * Returns array of eZTagsObject objects for given keyword
     *
     * @static
     *
     * @param mixed $keyword
     * @param bool $mainTranslation
     *
     * @return eZTagsObject[]
     */
    static public function fetchByKeyword( $keyword, $mainTranslation = false )
    {
        return self::fetchList( array( 'keyword' => $keyword ), null, null, $mainTranslation );
    }

    /**
     * Returns the array of eZTagsObject objects for given path string
     *
     * @static
     *
     * @param string $pathString
     * @param bool $mainTranslation
     *
     * @return eZTagsObject[]
     */
    static public function fetchByPathString( $pathString, $mainTranslation = false )
    {
        return self::fetchList( array( 'path_string' => array( 'like', $pathString . '%' ),
                                       'main_tag_id' => 0 ), null, null, $mainTranslation );
    }

    /**
     * Fetches tag by remote ID
     *
     * @static
     *
     * @param string $remoteID
     * @param bool $mainTranslation
     *
     * @return eZTagsObject|null
     */
    static public function fetchByRemoteID( $remoteID, $mainTranslation = false )
    {
        $tagsList = self::fetchList( array( 'remote_id' => $remoteID ), null, null, $mainTranslation );

        if ( is_array( $tagsList ) && !empty( $tagsList ) )
            return $tagsList[0];

        return null;
    }

    /**
     * Fetches tag by URL
     *
     * @static
     *
     * @param string $url
     * @param bool $mainTranslation
     *
     * @return eZTagsObject|null
     */
    static public function fetchByUrl( $url, $mainTranslation = false )
    {
        $urlArray = is_array( $url ) ? $url : explode( '/', ltrim( $url, '/' ) );
        if ( !is_array( $urlArray ) || empty( $urlArray ) )
            return null;

        $parentID = 0;
        for ( $i = 0; $i < count( $urlArray ) - 1; $i++ )
        {
            /** @var eZTagsObject[] $tags */
            $tags = self::fetchList(
                array(
                    'parent_id' => $parentID,
                    'main_tag_id' => 0,
                    'keyword' => urldecode( trim( $urlArray[$i] ) )
                ),
                null,
                null,
                $mainTranslation
            );

            if ( !is_array( $tags ) || empty( $tags ) )
                return null;

            $parentID = $tags[0]->attribute( 'id' );
        }

        $tags = self::fetchList(
            array(
                'parent_id' => $parentID,
                'keyword' => urldecode( trim( $urlArray[count( $urlArray ) - 1] ) )
            ),
            null,
            null,
            $mainTranslation
        );

        if ( !is_array( $tags ) || empty( $tags ) )
            return null;

        return $tags[0];
    }

    /**
     * Recursively deletes all tags below this tag, including self
     */
    public function recursivelyDeleteTag()
    {
        foreach ( $this->getChildren( 0, null, true ) as $child )
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

    /**
     * Moves all children of this tag below another tag
     *
     * @param eZTagsObject $targetTag
     */
    public function moveChildrenBelowAnotherTag( eZTagsObject $targetTag )
    {
        $currentTime = time();
        $children = $this->getChildren( 0, null, true );
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

    /**
     * Transfers all objects related to this tag, to another tag
     *
     * @param eZTagsObject|int $destination
     */
    public function transferObjectsToAnotherTag( $destination )
    {
        if ( !$destination instanceof self )
        {
            $destination = self::fetchWithMainTranslation( (int) $destination );
            if ( !$destination instanceof self )
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

    /**
     * Removes self, while also removing related translations and links to objects
     *
     * @param mixed $conditions
     * @param mixed $extraConditions
     */
    public function remove( $conditions = null, $extraConditions = null )
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
     *
     * @param int $tagID
     * @param string $keyword
     * @param int $parentID
     *
     * @return bool
     */
    static public function exists( $tagID, $keyword, $parentID )
    {
        $db = eZDB::instance();
        $sql = "SELECT COUNT(*) AS row_count FROM eztags, eztags_keyword
                WHERE eztags.id = eztags_keyword.keyword_id AND
                eztags.parent_id = " . (int) $parentID . " AND
                eztags.id <> " . (int) $tagID . " AND
                eztags_keyword.keyword LIKE '" . $db->escapeString( $keyword ) . "'";

        $result = $db->arrayQuery( $sql );

        if ( is_array( $result ) && !empty( $result ) )
        {
            if ( (int) $result[0]['row_count'] > 0 )
                return true;
        }

        return false;
    }

    /**
     * Fetches subtree of tags by specified parameters
     *
     * @static
     *
     * @param array $params
     * @param int $tagID
     *
     * @return eZTagsObject[]
     */
    static public function subTreeByTagID( $params = array(), $tagID = 0 )
    {
        if ( !is_numeric( $tagID ) || (int) $tagID < 0 )
            return false;

        $tag = self::fetch( (int) $tagID );
        if ( (int) $tagID > 0 && !$tag instanceof self )
            return false;

        if ( $tag instanceof self && $tag->attribute( 'main_tag_id' ) != 0 )
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
            if ( $tag instanceof self )
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

        $fetchResults = self::fetchList( $fetchParams, $limits, $sorts );

        if ( is_array( $fetchResults ) && !empty( $fetchResults ) )
            return $fetchResults;

        return false;
    }

    /**
     * Fetches subtree tag count by specified parameters
     *
     * @static
     *
     * @param array $params
     * @param int $tagID
     *
     * @return int
     */
    static public function subTreeCountByTagID( $params = array(), $tagID = 0 )
    {
        if ( !is_numeric( $tagID ) || (int) $tagID < 0 )
            return 0;

        $tag = self::fetch( (int) $tagID );
        if ( (int) $tagID > 0 && !$tag instanceof self )
            return 0;

        if ( $tag instanceof self && $tag->attribute( 'main_tag_id' ) != 0 )
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
            if ( $tag instanceof self )
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

    /**
     * Generates module result path for this tag, used in all module views
     *
     * @static
     *
     * @param mixed $tag
     * @param mixed $urlToGenerate
     * @param mixed $textPart
     * @param bool $mainTranslation
     *
     * @return array
     */
    static public function generateModuleResultPath( $tag = false, $urlToGenerate = null, $textPart = false, $mainTranslation = true )
    {
        $moduleResultPath = array();

        if ( is_string( $textPart ) )
        {
            $moduleResultPath[] = array( 'text'   => $textPart,
                                         'url'    => false );
        }

        if ( $tag instanceof self )
        {
            /** @var eZTagsObject $tag */
            $moduleResultPath[] = array( 'tag_id' => $tag->attribute( 'id' ),
                                         'text'   => $tag->attribute( 'keyword' ),
                                         'url'    => false );

            /** @var eZTagsObject[] $path */
            $path = $tag->getPath( true, $mainTranslation );
            if ( is_array( $path ) && !empty( $path ) )
            {
                foreach ( $path as $pathElement )
                {
                    // if $urlToGenerate === null, generate no urls
                    $url = false;
                    if ( $urlToGenerate !== null )
                    {
                        // if true generate nice urls
                        if ( $urlToGenerate )
                            $url = $pathElement->getUrl();
                        // else generate urls with ID
                        else
                            $url = 'tags/id/' . $pathElement->attribute( 'id' );
                    }

                    $moduleResultPath[] = array( 'tag_id' => $pathElement->attribute( 'id' ),
                                                 'text'   => $pathElement->attribute( 'keyword' ),
                                                 'url'    => $url );
                }
            }
        }

        return array_reverse( $moduleResultPath );
    }

    /**
     * Returns tag translation for provided locale
     *
     * @param string $locale
     *
     * @return eZTagsKeyword
     */
    public function translationByLocale( $locale )
    {
        return eZTagsKeyword::fetch( $this->attribute( 'id' ), $locale );
    }

    /**
     * Returns all tag translations
     *
     * @return eZTagsKeyword[]
     */
    public function getTranslations()
    {
        return eZTagsKeyword::fetchByTagID( $this->attribute( 'id' ) );
    }

    /**
     * Returns count of tag translations
     *
     * @return int
     */
    public function getTranslationsCount()
    {
        return eZTagsKeyword::fetchCountByTagID( $this->attribute( 'id' ) );
    }

    /**
     * Returns the main translation of this tag
     *
     * @return eZTagsKeyword
     */
    public function getMainTranslation()
    {
        /** @var eZContentLanguage $language */
        $language = eZContentLanguage::fetch( $this->attribute( 'main_language_id' ) );
        if ( $language instanceof eZContentLanguage )
            return $this->translationByLocale( $language->attribute( 'locale' ) );

        return false;
    }

    /**
     * Returns translation of the tag for provided language ID
     *
     * @param int $languageID
     *
     * @return eZTagsKeyword
     */
    public function translationByLanguageID( $languageID )
    {
        /** @var eZContentLanguage $language */
        $language = eZContentLanguage::fetch( $languageID );
        if ( $language instanceof eZContentLanguage )
            return $this->translationByLocale( $language->attribute( 'locale' ) );

        return false;
    }

    /**
     * Returns the tag keyword, locale aware
     *
     * @param mixed $locale
     *
     * @return string
     */
    public function getKeyword( $locale = false )
    {
        if ( $this->attribute( 'id' ) == null )
            return $this->Keyword;

        $translation = $this->translationByLocale( $locale === false ? $this->CurrentLanguage : $locale );
        if ( $translation instanceof eZTagsKeyword )
            return $translation->attribute( 'keyword' );

        return '';
    }

    /**
     * Returns the array of eZTagsKeyword->languageName() arrays, for every translation of the tag
     *
     * @return array
     */
    public function languageNameArray()
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

    /**
     * Returns the current language (locale) of the tag
     *
     * @return string
     */
    public function getCurrentLanguage()
    {
        return $this->CurrentLanguage;
    }

    /**
     * Returns the list of locales for which this tag is translated
     *
     * @return array
     */
    public function getAvailableLanguages()
    {
        $languages = eZContentLanguage::decodeLanguageMask( $this->attribute( 'language_mask' ), true );
        return $languages['language_list'];
    }

    /**
     * Returns if this tag has translation specified by $locale
     *
     * @param string $locale
     *
     * @return bool
     */
    public function hasTranslation( $locale )
    {
        $availableLanguages = $this->getAvailableLanguages();
        return in_array( $locale, $availableLanguages );
    }

    /**
     * Sets the main translation of the tag to provided locale
     *
     * @param string $locale
     *
     * @return bool
     */
    public function updateMainTranslation( $locale )
    {
        $trans = $this->translationByLocale( $locale );
        /** @var eZContentLanguage $language */
        $language = eZContentLanguage::fetchByLocale( $locale );
        if ( !$trans instanceof eZTagsKeyword || !$language instanceof eZContentLanguage )
            return false;

        $this->setAttribute( 'main_language_id', $language->attribute( 'id' ) );
        $keyword = $this->getKeyword( $locale );
        $this->setAttribute( 'keyword', $keyword );
        $this->store();

        $isAlwaysAvailable = $this->isAlwaysAvailable();
        foreach ( $this->getTranslations() as $translation )
        {
            if ( !$isAlwaysAvailable )
                $languageID = (int) $translation->attribute( 'language_id' ) & ~1;
            else
            {
                if ( $translation->attribute( 'locale' ) != $language->attribute( 'locale' ) )
                    $languageID = (int) $translation->attribute( 'language_id' ) & ~1;
                else
                    $languageID = (int) $translation->attribute( 'language_id' ) | 1;
            }

            $translation->setAttribute( 'language_id', $languageID );
            $translation->store();
        }

        return true;
    }

    /**
     * Updates language mask of the tag based on current translations or provided language mask
     *
     * @param mixed $mask
     */
    public function updateLanguageMask( $mask = false )
    {
        if ( $mask === false )
        {
            $locales = array();
            foreach ( $this->getTranslations() as $translation )
            {
                $locales[] = $translation->attribute( 'locale' );
            }

            $mask = eZContentLanguage::maskByLocale( $locales, $this->isAlwaysAvailable() );
        }

        $this->setAttribute( 'language_mask', $mask );
        $this->store();
    }

    /**
     * Returns if this tag is always available
     *
     * @return bool
     */
    public function isAlwaysAvailable()
    {
        $zerothBit = (int) $this->attribute( 'language_mask' ) & 1;
        return $zerothBit > 0 ? true : false;
    }

    /**
     * Sets/unsets always available flag for this tag
     *
     * @param bool $alwaysAvailable
     */
    public function setAlwaysAvailable( $alwaysAvailable )
    {
        $languageMask = (int) $this->attribute( 'language_mask' ) & ~1;
        $zerothBit = $alwaysAvailable ? 1 : 0;

        $this->setAttribute( 'language_mask', $languageMask | $zerothBit );
        $this->store();

        $mainTranslation = $this->getMainTranslation();
        if ( $mainTranslation instanceof eZTagsKeyword )
        {
            foreach ( $this->getTranslations() as $translation )
            {
                if ( !$alwaysAvailable )
                    $languageID = (int) $translation->attribute( 'language_id' ) & ~1;
                else
                {
                    if ( $translation->attribute( 'locale' ) != $mainTranslation->attribute( 'locale' ) )
                        $languageID = (int) $translation->attribute( 'language_id' ) & ~1;
                    else
                        $languageID = (int) $translation->attribute( 'language_id' ) | 1;
                }

                $translation->setAttribute( 'language_id', $languageID );
                $translation->store();
            }
        }
    }

    /**
     * Recursively deletes all children tags of the given tag, including the given tag itself
     * Deprecated: see $this->recursivelyDeleteTag()
     *
     * @deprecated
     *
     * @static
     *
     * @param eZTagsObject $rootTag
     */
    static public function recursiveTagDelete( $rootTag )
    {
        if ( !$rootTag instanceof self )
            return;

        $rootTag->recursivelyDeleteTag();
    }

    /**
     * Moves all children tags of the provided tag to the new location
     * Deprecated: see $this->moveChildrenBelowAnotherTag()
     *
     * @deprecated
     *
     * @static
     *
     * @param eZTagsObject $tag
     * @param eZTagsObject $targetTag
     */
    static public function moveChildren( $tag, $targetTag )
    {
        if ( !$tag instanceof self )
            return;

        $tag->moveChildrenBelowAnotherTag( $targetTag );
    }

    /**
     * @var bool|string $CurrentLanguage
     */
    private $CurrentLanguage = false;
}
