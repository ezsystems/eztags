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
    function __construct( $row )
    {
        if ( !isset( $row['remote_id'] ) || !$row['remote_id'] )
        {
            $row['remote_id'] = self::generateRemoteID();
        }

        parent::__construct( $row );
    }

    /**
     * Returns the definition array for eZTagsObject
     *
     * @return array
     */
    static function definition()
    {
        return array( 'fields'              => array( 'id'          => array( 'name'     => 'ID',
                                                                              'datatype' => 'integer',
                                                                              'default'  => 0,
                                                                              'required' => true ),
                                                      'parent_id'   => array( 'name'     => 'ParentID',
                                                                              'datatype' => 'integer',
                                                                              'default'  => null,
                                                                              'required' => false ),
                                                      'main_tag_id' => array( 'name'     => 'MainTagID',
                                                                              'datatype' => 'integer',
                                                                              'default'  => null,
                                                                              'required' => false ),
                                                      'keyword'     => array( 'name'     => 'Keyword',
                                                                              'datatype' => 'string',
                                                                              'default'  => '',
                                                                              'required' => false ),
                                                      'depth'       => array( 'name'     => 'Depth',
                                                                              'datatype' => 'integer',
                                                                              'default'  => 1,
                                                                              'required' => false ),
                                                      'path_string' => array( 'name'     => 'PathString',
                                                                              'datatype' => 'string',
                                                                              'default'  => '',
                                                                              'required' => false ),
                                                      'modified'    => array( 'name'     => 'Modified',
                                                                              'datatype' => 'integer',
                                                                              'default'  => 0,
                                                                              'required' => false ),
                                                      'remote_id'   => array( 'name' => "RemoteID",
                                                                              'datatype' => 'string',
                                                                              'default' => '',
                                                                              'required' => true ), ),
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
                                                      'icon'                      => 'getIcon',
                                                      'url'                       => 'getUrl',
                                                      'is_synonym'                => 'isSynonym' ),
                      'keys'                => array( 'id' ),
                      'increment_key'       => 'id',
                      'class_name'          => 'eZTagsObject',
                      'sort'                => array( 'keyword' => 'asc' ),
                      'name'                => 'eztags' );
    }

    /**
     * Updates path string of the tag and all of it's children and synonyms.
     *
     * @param eZTagsObject $parentTag
     */
    function updatePathString( $parentTag )
    {
        $pathString = ( ( $parentTag instanceof eZTagsObject ) ? $parentTag->attribute( 'path_string' ) : '/' ) . $this->attribute( 'id' ) . '/';
        $this->setAttribute( 'path_string', $pathString );
        $this->store();

        foreach ( $this->getSynonyms() as $s )
        {
            $pathString = ( ( $parentTag instanceof eZTagsObject ) ? $parentTag->attribute( 'path_string' ) : '/' ) . $s->attribute( 'id' ) . '/';
            $s->setAttribute( 'path_string', $pathString );
            $s->store();
        }

        foreach ( $this->getChildren() as $c )
        {
            $c->updatePathString( $this );
        }
    }

    /**
     * Updates depth of the tag and all of it's children and synonyms.
     *
     * @param eZTagsObject $parentTag
     */
    function updateDepth( $parentTag )
    {
        $depth = ( $parentTag instanceof eZTagsObject ) ? (int) $parentTag->attribute( 'depth' ) + 1 : 1;

        $this->setAttribute( 'depth', $depth );
        $this->store();

        foreach ( $this->getSynonyms() as $s )
        {
            $s->setAttribute( 'depth', $depth );
            $s->store();
        }

        foreach ( $this->getChildren() as $c )
        {
            $c->updateDepth( $this );
        }
    }

    /**
     * Returns whether tag has a parent
     *
     * @return bool
     */
    function hasParent()
    {
        $count = eZPersistentObject::count( self::definition(), array( 'id' => $this->attribute( 'parent_id' ) ) );

        if ( $count > 0 )
        {
            return true;
        }

        return false;
    }

    /**
     * Returns tag parent
     *
     * @return eZTagsObject
     */
    function getParent()
    {
        return self::fetch( $this->attribute( 'parent_id' ) );
    }

    /**
     * Returns first level children tags
     *
     * @return array
     */
    function getChildren()
    {
        return self::fetchByParentID( $this->attribute( 'id' ) );
    }

    /**
     * Returns count of first level children tags
     *
     * @return integer
     */
    function getChildrenCount()
    {
        return self::childrenCountByParentID( $this->attribute( 'id' ) );
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
     * Returns the count of objects related to this tag
     *
     * @return int
     */
    function getRelatedObjectsCount()
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
        {
            return (int) $result[0]['count'];
        }

        return 0;
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
        else
        {
            return array();
        }
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
        else
        {
            return 0;
        }
    }

    /**
     * Checks if any of the parents have subtree limits defined
     *
     * @return bool
     */
    function isInsideSubTreeLimit()
    {
        $tag = $this;
        while ( $tag->hasParent() )
        {
            $tag = $tag->getParent();
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
    function getMainTag()
    {
        return self::fetch( $this->attribute( 'main_tag_id' ) );
    }

    /**
     * Returns synonyms for the tag
     *
     * @return array
     */
    function getSynonyms()
    {
        return self::fetchSynonyms( $this->attribute( 'id' ) );
    }

    /**
     * Returns synonym count for the tag
     *
     * @return array
     */
    function getSynonymsCount()
    {
        return self::synonymsCount( $this->attribute( 'id' ) );
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
        {
            $tag = $this->getMainTag();
        }
        else
        {
            $tag = $this;
        }

        if ( array_key_exists( $tag->attribute( 'id' ), $iconMap ) && !empty( $iconMap[$tag->attribute( 'id' )] ) )
        {
            return $iconMap[$tag->attribute( 'id' )];
        }

        while ( $tag->attribute( 'parent_id' ) > 0 )
        {
            $tag = $tag->getParent();
            if ( array_key_exists( $tag->attribute( 'id' ), $iconMap ) && !empty( $iconMap[$tag->attribute( 'id' )] ) )
            {
                return $iconMap[$tag->attribute( 'id' )];
            }
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

        while ( $tag->attribute( 'parent_id' ) > 0 )
        {
            $tag = $tag->getParent();
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
        {
            array_push( $pathArray, $this->attribute( 'main_tag_id' ) );
        }

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

        if ( eZINI::instance( 'site.ini' )->variable( 'SearchSettings', 'DelayedIndexing' ) !== 'disabled'
            || $eZTagsINI->variable( 'SearchSettings', 'ReindexWhenDelayedIndexingDisabled' ) == 'enabled' )
        {
            $relatedObjects = $this->getRelatedObjects();
            foreach ( $relatedObjects as $relatedObject )
            {
                eZContentOperationCollection::registerSearchObject( $relatedObject->attribute( 'id' ), $relatedObject->attribute( 'current_version' ) );
            }
        }
        else
        {
            eZHTTPTool::instance()->setSessionVariable( 'eZTagsShowReindexMessage', 1 );
        }
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
        return eZPersistentObject::fetchObject( self::definition(), null, array( 'id' => $id ) );
    }

    /**
     * Returns array of eZTagsObject objects for given params
     *
     * @static
     * @param array $params
     * @param array $limits
     * @return array
     */
    static function fetchList( $params, $limits = null, $asObject = true, $sorts = null )
    {
        $tagsList = eZPersistentObject::fetchObjectList( self::definition(), null, $params, $sorts, $limits );

        if ( $asObject )
        {
            return $tagsList;
        }

        $tagsArray = array();
        foreach ( $tagsList as $tag )
        {
            $tagsArray[] = array( 'name' => $tag->attribute( 'keyword' ), 'id' => $tag->attribute( 'id' ) );
        }

        return $tagsArray;
    }

    /**
     * Returns count of eZTagsObject objects for given params
     *
     * @static
     * @param mixed $params
     * @return integer
     */
    static function fetchListCount( $params )
    {
        return eZPersistentObject::count( self::definition(), $params );
    }

    /**
     * Returns array of eZTagsObject objects for given parent ID
     *
     * @static
     * @param integer $parentID
     * @return array
     */
    static function fetchByParentID( $parentID )
    {
        return eZPersistentObject::fetchObjectList( self::definition(), null, array( 'parent_id' => $parentID, 'main_tag_id' => 0 ) );
    }

    /**
     * Returns count of eZTagsObject objects for given parent ID
     *
     * @static
     * @param integer $parentID
     * @return integer
     */
    static function childrenCountByParentID( $parentID )
    {
        return eZPersistentObject::count( self::definition(), array( 'parent_id' => $parentID, 'main_tag_id' => 0 ) );
    }

    /**
     * Returns array of eZTagsObject objects that are synonyms of provided tag ID
     *
     * @static
     * @param integer $mainTagID
     * @return array
     */
    static function fetchSynonyms( $mainTagID )
    {
        return eZPersistentObject::fetchObjectList( self::definition(), null, array( 'main_tag_id' => $mainTagID ) );
    }

    /**
     * Returns count of eZTagsObject objects that are synonyms of provided tag ID
     *
     * @static
     * @param integer $mainTagID
     * @return integer
     */
    static function synonymsCount( $mainTagID )
    {
        return eZPersistentObject::count( self::definition(), array( 'main_tag_id' => $mainTagID ) );
    }

    /**
     * Returns array of eZTagsObject objects for given keyword
     *
     * @static
     * @param mixed $keyword
     * @return array
     */
    static function fetchByKeyword( $keyword )
    {
        $cond = $customCond = null;

        if ( strpos( $keyword, '*' ) !== false )
            $customCond = self::generateCustomCondition( $keyword );
        else
            $cond = array( 'keyword' => $keyword );

        return eZPersistentObject::fetchObjectList( self::definition(),
                                                    null,
                                                    $cond,
                                                    null,
                                                    null,
                                                    true,
                                                    false,
                                                    null,
                                                    null,
                                                    $customCond );
    }

    /**
     * Returns a custom conditional string for wildcard searching (copied from
     * eZContentObjectTreeNode).
     *
     * @static
     * @param string $keyword
     * @return string
     */
    static private function generateCustomCondition( $keyword )
    {
        $keyword = preg_replace( array( '#%#m',
                                        '#(?<!\\\\)\\*#m',
                                        '#(?<!\\\\)\\\\\\*#m',
                                        '#\\\\\\\\#m' ),
                                 array( '\\%',
                                        '%',
                                        '*',
                                        '\\\\' ), $keyword );
        $db = eZDB::instance();
        $keyword = $db->escapeString( $keyword );
        return " WHERE eztags.keyword LIKE '$keyword'";
    }

    /**
     * Returns the array of eZTagsObject objects for given path string
     *
     * @static
     * @param string $pathString
     * @return array
     */
    static function fetchByPathString( $pathString )
    {
        return eZPersistentObject::fetchObjectList( self::definition(), null,
                                                    array( 'path_string' => array( 'like', $pathString . '%' ),
                                                           'main_tag_id' => 0 ) );
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
     * Recursively deletes all children tags of the given tag, including the given tag itself
     *
     * @static
     * @param eZTagsObject $rootTag
     */
    static function recursiveTagDelete( $rootTag )
    {
        $children = self::fetchByParentID( $rootTag->attribute( 'id' ) );

        foreach ( $children as $child )
        {
            self::recursiveTagDelete( $child );
        }

        $rootTag->registerSearchObjects();
        foreach ( $rootTag->getTagAttributeLinks() as $tagAttributeLink )
        {
            $tagAttributeLink->remove();
        }

        $synonyms = $rootTag->getSynonyms();
        foreach ( $synonyms as $synonym )
        {
            foreach ( $synonym->getTagAttributeLinks() as $tagAttributeLink )
            {
                $tagAttributeLink->remove();
            }

            $synonym->remove();
        }

        $rootTag->remove();
    }

    /**
     * Moves all children tags of the provided tag to the new location
     *
     * @static
     * @param eZTagsObject $tag
     * @param eZTagsObject $targetTag
     */
    static function moveChildren( $tag, $targetTag )
    {
        $currentTime = time();
        $children = $tag->getChildren();
        foreach ( $children as $child )
        {
            $childSynonyms = $child->getSynonyms();
            foreach ( $childSynonyms as $childSynonym )
            {
                $childSynonym->setAttribute( 'parent_id', $targetTag->attribute( 'id' ) );
                $childSynonym->store();
            }

            $child->setAttribute( 'parent_id', $targetTag->attribute( 'id' ) );
            $child->Modified = $currentTime;
            $child->store();
            $child->updatePathString( $targetTag );
            $child->updateDepth( $targetTag );
        }
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

    /**
     * Fetches Tag by remote_id
     * @param string $remoteID
     * @return eZTagsObject
     */
    static function fetchByRemoteID( $remoteID )
    {
        return eZPersistentObject::fetchObject( self::definition(), null, array(
            'remote_id' => $remoteID
        ) );
    }

    /**
     * Backward compatible remoteID generator
     * @return string
     */
    static function generateRemoteID()
    {
        //eZRemoteIdUtility introduced in eZPublish version 4.5
        if ( method_exists( 'eZRemoteIdUtility', 'generate' ) )
        {
            return eZRemoteIdUtility::generate( 'tag' );
        }
        else
        {
           return md5( (string) mt_rand() . (string) time() );
        }
    }

    /**
     * Tells wether tag object is a synonym of another tag object
     * @return boolean
     */
    function isSynonym()
    {
        return $this->attribute( 'main_tag_id' ) && $this->attribute( 'main_tag_id' ) !== $this->attribute( 'id' );
    }
}

?>
