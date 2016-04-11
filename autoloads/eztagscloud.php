<?php

/**
 * eZTagsCloud class implements eztagscloud template operator method
 */
class eZTagsCloud
{
    /**
     * Return an array with the list of template operator names
     *
     * @return array
     */
    public function operatorList()
    {
        return array( 'eztagscloud' );
    }

    /**
     * Return true to tell the template engine that the parameter list exists per operator type,
     * this is needed for operator classes that have multiple operators.
     *
     * @return bool
     */
    public function namedParameterPerOperator()
    {
        return true;
    }

    /**
     * Returns an array of named parameters, this allows for easier retrieval
     * of operator parameters. This also requires the function modify() has an extra
     * parameter called $namedParameters.
     *
     * @return array
     */
    public function namedParameterList()
    {
        return array( 'eztagscloud' => array( 'params' => array( 'type'     => 'array',
                                                                 'required' => false,
                                                                 'default'  => array() ) ) );
    }

    /**
     * Executes the PHP function for the operator cleanup and modifies $operatorValue.
     *
     * @param eZTemplate $tpl
     * @param string $operatorName
     * @param array $operatorParameters
     * @param string $rootNamespace
     * @param string $currentNamespace
     * @param mixed $operatorValue
     * @param array $namedParameters
     */
    public function modify( $tpl, $operatorName, $operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {
        switch ( $operatorName )
        {
            case 'eztagscloud':
            {
                $searchEngine = eZINI::instance()->variable( 'SearchSettings', 'SearchEngine' );
                if ( class_exists( 'eZSolr' ) && $searchEngine == 'ezsolr'
                    && eZINI::instance( 'eztags.ini' )->variable( 'GeneralSettings', 'TagCloudOverSolr' ) === 'enabled' )
                {
                    $tagCloud = $this->solrTagCloud( $namedParameters['params'] );
                }
                else
                {
                    $tagCloud = $this->tagCloud( $namedParameters['params'] );
                }

                $tpl = eZTemplate::factory();
                $tpl->setVariable( 'tag_cloud', $tagCloud );
                $operatorValue = $tpl->fetch( 'design:tagcloud.tpl' );
            } break;
        }
    }

    /**
     * Returns the tag cloud for specified parameters using eZ Publish database
     *
     * @param array $params
     *
     * @return array
     */
    private function tagCloud( $params )
    {
        $parentNodeID = 0;
        $classIdentifier = '';
        $classIdentifierSQL = '';
        $pathString = '';
        $parentNodeIDSQL = '';
        $dbParams = array();
        $orderBySql = 'ORDER BY eztags.keyword ASC';

        if ( isset( $params['class_identifier'] ) )
            $classIdentifier = $params['class_identifier'];

        if ( isset( $params['parent_node_id'] ) )
            $parentNodeID = $params['parent_node_id'];

        if ( isset( $params['limit'] ) )
            $dbParams['limit'] = $params['limit'];

        if ( isset( $params['offset'] ) )
            $dbParams['offset'] = $params['offset'];

        if ( isset( $params['sort_by'] ) && is_array( $params['sort_by'] ) && !empty( $params['sort_by'] ) )
        {
            $orderBySql = 'ORDER BY ';
            $orderArr = is_string( $params['sort_by'][0] ) ? array( $params['sort_by'] ) : $params['sort_by'];

            foreach ( $orderArr as $key => $order )
            {
                if ( $key !== 0 ) $orderBySql .= ', ';
                $direction = isset( $order[1] ) ? $order[1] : false;
                switch( $order[0] )
                {
                    case 'keyword':
                    {
                        $orderBySql .= 'eztags.keyword ' . ( $direction ? 'ASC' : 'DESC' );
                    }break;
                    case 'count':
                    {
                        $orderBySql .= 'keyword_count ' . ( $direction ? 'ASC' : 'DESC' );
                    }break;
                }
            }
        }

        $db = eZDB::instance();

        if ( $classIdentifier )
        {
            $classID = eZContentObjectTreeNode::classIDByIdentifier( $classIdentifier );
            $classIdentifierSQL = "AND ezcontentobject.contentclass_id = '" . $classID . "'";
        }

        if ( $parentNodeID )
        {
            $node = eZContentObjectTreeNode::fetch( $parentNodeID );
            if ( $node )
                $pathString = "AND ezcontentobject_tree.path_string like '" . $node->attribute( 'path_string' ) . "%'";
            $parentNodeIDSQL = "AND ezcontentobject_tree.node_id != " . (int) $parentNodeID;
        }

        $showInvisibleNodesCond = eZContentObjectTreeNode::createShowInvisibleSQLString( true, false );
        $limitation = false;
        $limitationList = eZContentObjectTreeNode::getLimitationList( $limitation );
        $sqlPermissionChecking = eZContentObjectTreeNode::createPermissionCheckingSQL( $limitationList );

        $languageFilter = 'AND ' . eZContentLanguage::languagesSQLFilter( 'ezcontentobject' );
        $languageFilter .= 'AND ' . eZContentLanguage::languagesSQLFilter( 'ezcontentobject_attribute', 'language_id' );

        $rs = $db->arrayQuery( "SELECT eztags.id, eztags.keyword, COUNT(DISTINCT ezcontentobject.id) AS keyword_count
                                FROM eztags_attribute_link
                                LEFT JOIN ezcontentobject_attribute
                                    ON eztags_attribute_link.objectattribute_id = ezcontentobject_attribute.id
                                    AND eztags_attribute_link.objectattribute_version = ezcontentobject_attribute.version
                                LEFT JOIN ezcontentobject
                                    ON ezcontentobject_attribute.contentobject_id = ezcontentobject.id
                                LEFT JOIN ezcontentobject_tree
                                    ON ezcontentobject_attribute.contentobject_id = ezcontentobject_tree.contentobject_id
                                LEFT JOIN eztags
                                    ON eztags.id = eztags_attribute_link.keyword_id
                                LEFT JOIN eztags_keyword
                                    ON eztags.id = eztags_keyword.keyword_id
                                $sqlPermissionChecking[from]
                                WHERE " . eZContentLanguage::languagesSQLFilter( 'eztags' ) . "
                                    AND " . eZContentLanguage::sqlFilter( 'eztags_keyword', 'eztags' ) . "
                                    AND ezcontentobject.status = " . eZContentObject::STATUS_PUBLISHED . "
                                    AND ezcontentobject_attribute.version = ezcontentobject.current_version
                                    AND ezcontentobject_tree.main_node_id = ezcontentobject_tree.node_id
                                    $pathString
                                    $parentNodeIDSQL
                                    $classIdentifierSQL
                                    $showInvisibleNodesCond
                                    $sqlPermissionChecking[where]
                                    $languageFilter
                                GROUP BY eztags.id, eztags.keyword
                                $orderBySql", $dbParams );

        $tagsCountList = array();
        foreach( $rs as $row )
        {
            $tagsCountList[$row['id']] = $row['keyword_count'];
        }

        if ( empty( $tagsCountList ) )
            return array();
        
        /** @var eZTagsObject[] $tagObjects */
        $tagObjects = eZTagsObject::fetchList( array( 'id' => array( array_keys( $tagsCountList ) ) ) );
        if ( !is_array( $tagObjects ) || empty( $tagObjects ) )
            return array();

        $tagSortArray = array();
        $tagKeywords = array();
        $tagCounts = array();
        foreach ( $tagObjects as $tag )
        {
            $tagKeyword = $tag->attribute( 'keyword' );
            $tagCount = $tagsCountList[$tag->attribute( 'id' )];

            $tagSortArray[] = array(
                'keyword'   => $tagKeyword,
                'count'     => $tagCount,
                'tag'       => $tag
            );

            $tagKeywords[] = $tagKeyword;
            $tagCounts[] = $tagCount;
        }

        if ( isset( $params['post_sort_by'] ) )
        {
            if ( $params['post_sort_by'] === 'keyword' )
                array_multisort( $tagKeywords, SORT_ASC, SORT_LOCALE_STRING, $tagSortArray );
            else if ( $params['post_sort_by'] === 'keyword_reverse' )
                array_multisort( $tagKeywords, SORT_DESC, SORT_LOCALE_STRING, $tagSortArray );
            else if ( $params['post_sort_by'] === 'count' )
                array_multisort( $tagCounts, SORT_ASC, SORT_NUMERIC, $tagSortArray );
            else if ( $params['post_sort_by'] === 'count_reverse' )
                array_multisort( $tagCounts, SORT_DESC, SORT_NUMERIC, $tagSortArray );
        }

        $this->normalizeTagCounts( $tagSortArray, $tagCounts );

        return $tagSortArray;
    }

    /**
     * Returns the tag cloud for specified parameters using eZ Find
     *
     * @param array $params
     *
     * @return array
     */
    private function solrTagCloud( $params )
    {
        $offset = 0;
        if( isset( $params['offset'] ) && is_numeric( $params['offset'] ) )
            $offset = (int) $params['offset'];

        // It seems that Solr doesn't like PHP_INT_MAX constant on 64bit operating systems
        $limit = 1000000;
        if( isset( $params['limit'] ) && is_numeric( $params['limit'] ) )
            $limit = (int) $params['limit'];

        $searchFilter = array();

        if ( isset( $params['class_identifier'] ) )
        {
            if ( !is_array( $params['class_identifier'] ) )
                $params['class_identifier'] = array( $params['class_identifier'] );

            if ( !empty( $params['class_identifier'] ) )
                $searchFilter['meta_class_identifier_ms'] = '(' . implode( ' OR ', $params['class_identifier'] ) . ')';
        }

        if ( isset( $params['parent_node_id'] ) )
            $searchFilter['meta_path_si'] = (int) $params['parent_node_id'];

        $solrSearch = new eZSolr();
        $solrParams = array(
            'SearchOffset'   => 0,
            // It seems that Solr doesn't like PHP_INT_MAX constant on 64bit operating systems
            'SearchLimit'    => 1000000,
            'Facet'          => array(
                // We don't want to limit max facet result number since we limit it later anyways
                array( 'field' => 'ezf_df_tag_ids', 'limit' => 1000000 )
            ),
            'Filter'         => $searchFilter,
            'QueryHandler'   => 'ezpublish',
            'AsObjects'      => false
        );

        $searchResult = $solrSearch->search( '*:*', $solrParams );
        if ( !isset( $searchResult['SearchExtras'] ) || !$searchResult['SearchExtras'] instanceof ezfSearchResultInfo )
            return array();

        $facetResult = $searchResult['SearchExtras']->attribute( 'facet_fields' );
        if ( !is_array( $facetResult ) || empty( $facetResult[0]['countList'] ) )
            return array();

        $tagsCountList = $facetResult[0]['countList'];

        /** @var eZTagsObject[] $tags */
        $tags = eZTagsObject::fetchList( array( 'id' => array( array_keys( $tagsCountList ) ) ) );
        if ( !is_array( $tags ) || empty( $tags ) )
            return array();

        $tagSortArray = array();
        $tagKeywords = array();
        $tagCounts = array();
        foreach ( $tags as $tag )
        {
            $tagKeyword = $tag->attribute( 'keyword' );
            $tagCount = $tagsCountList[(int) $tag->attribute( 'id' )];

            $tagSortArray[] = array(
                'keyword'   => $tagKeyword,
                'count'     => $tagCount,
                'tag'       => $tag
            );

            $tagKeywords[] = $tagKeyword;
            $tagCounts[] = $tagCount;
        }

        // calling call_user_func_array requires all arguments to be references
        // this is the reason for $sortFlags array and $sortArgs[] = &....
        $sortArgs = array();
        $sortFlags = array( SORT_ASC, SORT_DESC, SORT_LOCALE_STRING, SORT_NUMERIC );
        if ( isset( $params['sort_by'] ) && is_array( $params['sort_by'] ) && !empty( $params['sort_by'] ) )
        {
            $params['sort_by'] = is_string( $params['sort_by'][0] ) ? array( $params['sort_by'] ) : $params['sort_by'];
            foreach ( $params['sort_by'] as $sortItem )
            {
                if ( is_array( $sortItem ) && !empty( $sortItem ) )
                {
                    switch ( $sortItem[0] )
                    {
                        case 'keyword':
                            $sortArgs[] = &$tagKeywords;
                            if ( isset( $sortItem[1] ) && $sortItem[1] )
                                $sortArgs[] = &$sortFlags[0];
                            else
                                $sortArgs[] = &$sortFlags[1];
                            $sortArgs[] = &$sortFlags[2];
                            break;
                        case 'count':
                            $sortArgs[] = &$tagCounts;
                            if ( isset( $sortItem[1] ) && $sortItem[1] )
                                $sortArgs[] = &$sortFlags[0];
                            else
                                $sortArgs[] = &$sortFlags[1];
                            $sortArgs[] = &$sortFlags[3];
                            break;
                    }
                }
            }
        }

        if ( empty( $sortArgs ) )
        {
            $sortArgs[] = &$tagKeywords;
            $sortArgs[] = &$sortFlags[0];
        }

        $sortArgs[] = &$tagSortArray;

        call_user_func_array( 'array_multisort', $sortArgs );

        $tagSortArray = array_slice( $tagSortArray, $offset, $limit );
        if ( empty( $tagSortArray ) )
            return array();

        $tagKeywords = array_slice( $tagKeywords, $offset, $limit );
        $tagCounts = array_slice( $tagCounts, $offset, $limit );

        if ( isset( $params['post_sort_by'] ) )
        {
            if ( $params['post_sort_by'] === 'keyword' )
                array_multisort( $tagKeywords, SORT_ASC, SORT_LOCALE_STRING, $tagSortArray );
            else if ( $params['post_sort_by'] === 'keyword_reverse' )
                array_multisort( $tagKeywords, SORT_DESC, SORT_LOCALE_STRING, $tagSortArray );
            else if ( $params['post_sort_by'] === 'count' )
                array_multisort( $tagCounts, SORT_ASC, SORT_NUMERIC, $tagSortArray );
            else if ( $params['post_sort_by'] === 'count_reverse' )
                array_multisort( $tagCounts, SORT_DESC, SORT_NUMERIC, $tagSortArray );
        }

        $this->normalizeTagCounts( $tagSortArray, $tagCounts );

        return $tagSortArray;
    }

    /**
     * Normalizes the count of tags to be able to be displayed properly on the page
     *
     * @param array $tagsArray
     * @param array $tagCounts
     */
    private function normalizeTagCounts( &$tagsArray, $tagCounts )
    {
        $maxFontSize = 200;
        $minFontSize = 100;

        $maxCount = max( $tagCounts );
        $minCount = min( $tagCounts );

        $spread = $maxCount - $minCount;
        if ( $spread == 0 )
            $spread = 1;

        $step = ( $maxFontSize - $minFontSize ) / ( $spread );

        foreach ( $tagsArray as $index => $tagItem )
        {
            $tagsArray[$index]['font_size'] = $minFontSize + ( ( $tagItem['count'] - $minCount ) * $step );
        }
    }
}
