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
                                                      'path_string' => array( 'name'     => 'PathString',
                                                                              'datatype' => 'string',
                                                                              'default'  => '',
                                                                              'required' => false ),
                                                      'modified'    => array( 'name'     => 'Modified',
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
                                                      'url'                       => 'getUrl' ),
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
        $this->PathString = ( ( $parentTag instanceof eZTagsObject ) ? $parentTag->PathString : '/' ) . $this->ID . '/';
        $this->store();

        foreach ( $this->getSynonyms() as $s )
        {
            $s->PathString = ( ( $parentTag instanceof eZTagsObject ) ? $parentTag->PathString : '/' ) . $s->ID . '/';
            $s->store();
        }

        foreach ( $this->getChildren() as $c )
        {
            $c->updatePathString( $this );
        }
    }

    /**
     * Returns whether tag has a parent
     *
     * @return bool
     */
    function hasParent()
    {
        $count = eZPersistentObject::count( self::definition(), array( 'id' => $this->ParentID ) );

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
        return self::fetch( $this->ParentID );
    }

    /**
     * Returns first level children tags
     *
     * @return array
     */
    function getChildren()
    {
        return self::fetchByParentID( $this->ID );
    }

    /**
     * Returns count of first level children tags
     *
     * @return integer
     */
    function getChildrenCount()
    {
        return self::childrenCountByParentID( $this->ID );
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
        $tagID = $this->ID;

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
        if ( $this->MainTagID == 0 )
        {
            return eZPersistentObject::fetchObjectList( eZContentClassAttribute::definition(), null,
                                                        array( 'data_type_string'              => 'eztags',
                                                               eZTagsType::SUBTREE_LIMIT_FIELD => $this->ID,
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
        if ( $this->MainTagID == 0 )
        {
            return eZPersistentObject::count( eZContentClassAttribute::definition(),
                                              array( 'data_type_string'              => 'eztags',
                                                     eZTagsType::SUBTREE_LIMIT_FIELD => $this->ID,
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
        return self::fetch( $this->MainTagID );
    }

    /**
     * Returns synonyms for the tag
     *
     * @return array
     */
    function getSynonyms()
    {
        return self::fetchSynonyms( $this->ID );
    }

    /**
     * Returns synonym count for the tag
     *
     * @return array
     */
    function getSynonymsCount()
    {
        return self::synonymsCount( $this->ID );
    }

    /**
     * Returns all links to objects for this tag
     *
     * @return array
     */
    function getTagAttributeLinks()
    {
        return eZTagsAttributeLinkObject::fetchByTagID( $this->ID );
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

        if ( $this->MainTagID > 0 )
        {
            $tag = $this->getMainTag();
        }
        else
        {
            $tag = $this;
        }

        if ( array_key_exists( $tag->ID, $iconMap ) && !empty( $iconMap[$tag->ID] ) )
        {
            return $iconMap[$tag->ID];
        }

        while ( $tag->ParentID > 0 )
        {
            $tag = $tag->getParent();
            if ( array_key_exists( $tag->ID, $iconMap ) && !empty( $iconMap[$tag->ID] ) )
            {
                return $iconMap[$tag->ID];
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
        $url = urlencode( $this->Keyword );
        $tag = $this;

        while ( $tag->ParentID > 0 )
        {
            $tag = $tag->getParent();
            $url = urlencode( $tag->Keyword ) . '/' . $url;
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
        $pathArray = explode( '/', trim( $this->PathString, '/' ) );

        if ( $this->MainNodeID > 0 )
        {
            array_push( $pathArray, $this->MainNodeID );
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

        if ( eZINI::instance( 'site.ini' )->variable( 'SearchSettings', 'DelayedIndexing' ) == 'enabled'
            || $eZTagsINI->variable( 'SearchSettings', 'ReindexWhenDelayedIndexingDisabled' ) == 'enabled' )
        {
            $relatedObjects = $this->getRelatedObjects();
            foreach ( $relatedObjects as $relatedObject )
            {
                eZContentOperationCollection::registerSearchObject( $relatedObject->ID, $relatedObject->CurrentVersion );
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
    static function fetchList( $params, $limits = null, $asObject = true )
    {
        $tagsList = eZPersistentObject::fetchObjectList( self::definition(), null, $params, null, $limits );

        if ( $asObject )
        {
            return $tagsList;
        }

        $tagsArray = array();
        foreach ( $tagsList as $tag )
        {
            $tagsArray[] = array( 'name' => $tag->Keyword, 'id' => $tag->ID );
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
        return eZPersistentObject::fetchObjectList( self::definition(), null, array( 'keyword' => $keyword ) );
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
        $children = self::fetchByParentID( $rootTag->ID );

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
                $childSynonym->ParentID = $targetTag->ID;
                $childSynonym->store();
            }

            $child->ParentID = $targetTag->ID;
            $child->Modified = $currentTime;
            $child->store();
            $child->updatePathString( $targetTag );
        }
    }
}

?>
