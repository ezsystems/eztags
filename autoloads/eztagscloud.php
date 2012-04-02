<?php

class eZTagsCloud
{
    function operatorList()
    {
        return array( 'eztagscloud' );
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array( 'eztagscloud' => array( 'params' => array( 'type'     => 'array',
                                                                 'required' => false,
                                                                 'default'  => array() ) ) );
    }

    function modify( $tpl, $operatorName, $operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {
        switch ( $operatorName )
        {
            case 'eztagscloud':
            {

                $tags = array();
                $tagCloud = array();
                $parentNodeID = 0;
                $classIdentifier = '';
                $classIdentifierSQL = '';
                $pathString = '';
                $parentNodeIDSQL = '';
                $dbParams = array();
                $params = $namedParameters['params'];
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
                                $orderBySql .= 'eztags.keyword ' . ( $direction ? 'ASC' : 'DESC');
                            }break;
                            case 'count':
                            {
                                $orderBySql .= 'keyword_count ' . ( $direction ? 'ASC' : 'DESC');
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

                $rs = $db->arrayQuery( "SELECT eztags.id, eztags.keyword, count(eztags.keyword) AS keyword_count
                                        FROM eztags $sqlPermissionChecking[from], eztags_attribute_link
                                        LEFT JOIN ezcontentobject_attribute
                                            ON eztags_attribute_link.objectattribute_id = ezcontentobject_attribute.id
                                            AND eztags_attribute_link.objectattribute_version = ezcontentobject_attribute.version
                                        LEFT JOIN ezcontentobject
                                            ON ezcontentobject_attribute.contentobject_id = ezcontentobject.id
                                        LEFT JOIN ezcontentobject_tree
                                            ON ezcontentobject_attribute.contentobject_id = ezcontentobject_tree.contentobject_id
                                        WHERE eztags.id = eztags_attribute_link.keyword_id
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

                foreach( $rs as $row )
                {
                    $tags[$row['id']] = $row['keyword_count'];
                }

                // To be able to combine count sorting with keyword sorting
                // without being limited by sql LIMIT result clipping
                if ( isset( $params['post_sort_by'] ) )
                {
                    if ( $params['post_sort_by'] === 'keyword' )
                        ksort( $tags, SORT_LOCALE_STRING );
                    else if ( $params['post_sort_by'] === 'keyword_reverse' )
                        krsort( $tags, SORT_LOCALE_STRING );
                    else if ( $params['post_sort_by'] === 'count' )
                        asort( $tags, SORT_NUMERIC );
                    else if ( $params['post_sort_by'] === 'count_reverse' )
                        arsort( $tags, SORT_NUMERIC );
                }

                $maxFontSize = 200;
                $minFontSize = 100;

                $maxCount = 0;
                $minCount = 0;

                if ( !empty( $tags ) )
                {
                    $maxCount = max( array_values( $tags ) );
                    $minCount = min( array_values( $tags ) );
                }

                $spread = $maxCount - $minCount;
                if ( $spread == 0 )
                    $spread = 1;

                $step = ( $maxFontSize - $minFontSize ) / ( $spread );

                foreach ( $tags as $key => $value )
                {
                    $size = $minFontSize + ( ( $value - $minCount ) * $step );
                    $tagCloud[] = array( 'font_size' => $size,
                                         'count'     => $value,
                                         'id'        => $key );
                }

                $tpl = eZTemplate::factory();
                $tpl->setVariable( 'tag_cloud', $tagCloud );
                $operatorValue = $tpl->fetch( 'design:tagcloud.tpl' );
            } break;
        }
    }
}

?>
