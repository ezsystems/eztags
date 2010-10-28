<?php

/**
 * eZTagsNodeList class implements fetch functions for eztags
 * 
 */
class eZTagsNodeList
{
    /**
     * Returns array with one element containing the count of nodes
     * 
     * @static
     * @param string $alphabet
     * @param mixed $classid
     * @param integer $owner
     * @param integer $parentNodeID
     * @return array
     */
    static public function fetchNodeListCount( $alphabet,
                                $classid,
                                $owner = false,
                                $parentNodeID = false )
    {
        $classIDArray = array();
        if ( is_numeric( $classid ) )
        {
            $classIDArray = array( $classid );
        }
        else if ( is_array( $classid ) )
        {
            $classIDArray = $classid;
        }

        $showInvisibleNodesCond = eZContentObjectTreeNode::createShowInvisibleSQLString( true, false );
        $limitation = false;
        $limitationList = eZContentObjectTreeNode::getLimitationList( $limitation );
        $sqlPermissionChecking = eZContentObjectTreeNode::createPermissionCheckingSQL( $limitationList );

        $db = eZDB::instance();

        $alphabet = $db->escapeString( $alphabet );

        $sqlOwnerString = is_numeric( $owner ) ? "AND ezcontentobject.owner_id = '$owner'" : '';
        $parentNodeIDString = is_numeric( $parentNodeID ) ? "AND ezcontentobject_tree.parent_node_id = '$parentNodeID'" : '';

        $sqlClassIDs = '';
        if ( $classIDArray != null )
        {
            $sqlClassIDs = 'AND ' . $db->generateSQLINStatement( $classIDArray, 'ezcontentclass.id', false, false, 'int' ) . ' ';
        }

        $sqlToExcludeDuplicates = '';
        $sqlMatching = "eztags.keyword = '$alphabet'";

        $query = "SELECT COUNT($sqlToExcludeDuplicates ezcontentobject.id) AS count
                  FROM eztags, eztags_attribute_link,ezcontentobject_tree,ezcontentobject,ezcontentclass, ezcontentobject_attribute
                       $sqlPermissionChecking[from]
                  WHERE $sqlMatching
                  $showInvisibleNodesCond
                  $sqlPermissionChecking[where]
                  $sqlClassIDs
                  $sqlOwnerString
                  $parentNodeIDString
                  AND ezcontentclass.version=0
                  AND ezcontentobject.status=".eZContentObject::STATUS_PUBLISHED."
                  AND ezcontentobject_attribute.version=ezcontentobject.current_version
                  AND ezcontentobject_tree.main_node_id=ezcontentobject_tree.node_id
                  AND ezcontentobject_attribute.contentobject_id=ezcontentobject.id
                  AND ezcontentobject_tree.contentobject_id = ezcontentobject.id
                  AND ezcontentclass.id = ezcontentobject.contentclass_id
                  AND ezcontentobject_attribute.id=eztags_attribute_link.objectattribute_id
                  AND eztags_attribute_link.keyword_id = eztags.id";

        $keyWords = $db->arrayQuery( $query );
        // cleanup temp tables
        $db->dropTempTableList( $sqlPermissionChecking['temp_tables'] );

        return array( 'result' => $keyWords[0]['count'] );
    }

    /**
     * Returns array with one element containing keyword <-> node_id mappings
     * 
     * @static
     * @param string $alphabet
     * @param mixed $classid
     * @param integer $offset
     * @param integer $limit
     * @param integer $owner
     * @param array $sortBy
     * @param integer $parentNodeID
     * @return array
     */
    static public function fetchNodeList( $alphabet,
                           $classid,
                           $offset,
                           $limit,
                           $owner = false,
                           $sortBy = array(),
                           $parentNodeID = false )
    {
        $classIDArray = array();
        if ( is_numeric( $classid ) )
        {
            $classIDArray = array( $classid );
        }
        else if ( is_array( $classid ) )
        {
            $classIDArray = $classid;
        }

        $showInvisibleNodesCond = eZContentObjectTreeNode::createShowInvisibleSQLString( true, false );
        $limitation = false;
        $limitationList = eZContentObjectTreeNode::getLimitationList( $limitation );
        $sqlPermissionChecking = eZContentObjectTreeNode::createPermissionCheckingSQL( $limitationList );

        $db_params = array();
        $db_params['offset'] = $offset;
        $db_params['limit'] = $limit;

        $keywordNodeArray = array();
        $lastKeyword = '';

        $db = eZDB::instance();

        $sqlKeyword = 'eztags.keyword';

        $alphabet = $db->escapeString( $alphabet );

        $sortingInfo = array();
        $sortingInfo['attributeFromSQL'] = ', ezcontentobject_attribute a1';
        $sortingInfo['attributeWhereSQL'] = '';
        $sqlTarget = $sqlKeyword.',ezcontentobject_tree.node_id';

        if ( is_array( $sortBy ) && count ( $sortBy ) > 0 )
        {
            switch ( $sortBy[0] )
            {
                case 'keyword':
                case 'name':
                {
                    $sortingString = '';
                    if ( $sortBy[0] == 'name' )
                    {
                        $sortingString = 'ezcontentobject.name';
                        $sortingInfo['attributeTargetSQL'] = ', ' . $sortingString;
                    }
                    elseif ( $sortBy[0] == 'keyword' )
                    {
                        $sortingString = 'eztags.keyword';
                        $sortingInfo['attributeTargetSQL'] = '';
                    }

                    $sortOrder = true; // true is ascending
                    if ( isset( $sortBy[1] ) )
                        $sortOrder = $sortBy[1];
                    $sortingOrder = $sortOrder ? ' ASC' : ' DESC';
                    $sortingInfo['sortingFields'] = $sortingString . $sortingOrder;
                } break;
                case 'view_count':
                {
                    $sortingString = 'ezview_counter.count';
                    $sortingInfo['attributeTargetSQL'] = ', ' . $sortingString;

                    $sortOrder = true; // true is ascending
                    if ( isset( $sortBy[1] ) )
                        $sortOrder = $sortBy[1];
                    $sortingOrder = $sortOrder ? ' ASC' : ' DESC';
                    $sortingInfo['sortingFields'] = $sortingString . $sortingOrder;
                } break;
                case 'comment_count':
                {
                    $sortingString = 'comment_count';
                    $sortingInfo['attributeTargetSQL'] = '';

                    $sortOrder = true; // true is ascending
                    if ( isset( $sortBy[1] ) )
                        $sortOrder = $sortBy[1];
                    $sortingOrder = $sortOrder ? ' ASC' : ' DESC';
                    $sortingInfo['sortingFields'] = $sortingString . $sortingOrder;
                } break;
                default:
                {
                    $sortingInfo = eZContentObjectTreeNode::createSortingSQLStrings( $sortBy );

                    if ( $sortBy[0] == 'attribute' )
                    {
                        // if sort_by is 'attribute' we should add ezcontentobject_name to "FromSQL" and link to ezcontentobject
                        $sortingInfo['attributeFromSQL']  .= ', ezcontentobject_name, ezcontentobject_attribute a1';
                        $sortingInfo['attributeWhereSQL'] .= ' ezcontentobject.id = ezcontentobject_name.contentobject_id AND';
                        $sqlTarget = 'DISTINCT ezcontentobject_tree.node_id, '.$sqlKeyword;
                    }
                    else // for unique declaration
                        $sortingInfo['attributeFromSQL']  .= ', ezcontentobject_attribute a1';

                } break;
            }

            $sqlTarget .= $sortingInfo['attributeTargetSQL'];
        }
        else
        {
            $sortingInfo['sortingFields'] = 'eztags.keyword ASC';
        }
        $sortingInfo['attributeWhereSQL'] .= " a1.version=ezcontentobject.current_version
                                             AND a1.contentobject_id=ezcontentobject.id AND";

        $sqlOwnerString = is_numeric( $owner ) ? "AND ezcontentobject.owner_id = '$owner'" : '';
        $parentNodeIDString = is_numeric( $parentNodeID ) ? "AND ezcontentobject_tree.parent_node_id = '$parentNodeID'" : '';

        $sqlClassIDString = '';
        if ( is_array( $classIDArray ) and count( $classIDArray ) )
        {
            $sqlClassIDString = 'AND ' . $db->generateSQLINStatement( $classIDArray, 'ezcontentclass.id', false, false, 'int' ) . ' ';
        }

        $sqlMatching = "eztags.keyword = '$alphabet'";

        $query = "SELECT $sqlTarget, COUNT(ezcomment.id) AS comment_count
                  FROM eztags, eztags_attribute_link,ezcontentobject_tree
                       LEFT OUTER JOIN ezview_counter ON ezcontentobject_tree.main_node_id=ezview_counter.node_id,ezcontentobject
                       LEFT OUTER JOIN ezcomment ON ezcontentobject.id=ezcomment.contentobject_id,ezcontentclass
                       $sortingInfo[attributeFromSQL]
                       $sqlPermissionChecking[from]
                  WHERE
                  $sortingInfo[attributeWhereSQL]
                  $sqlMatching
                  $showInvisibleNodesCond
                  $sqlPermissionChecking[where]
                  $sqlClassIDString
                  $sqlOwnerString
                  $parentNodeIDString
                  AND ezcontentclass.version=0
                  AND ezcontentobject.status=".eZContentObject::STATUS_PUBLISHED."
                  AND ezcontentobject_tree.main_node_id=ezcontentobject_tree.node_id
                  AND ezcontentobject_tree.contentobject_id = ezcontentobject.id
                  AND ezcontentclass.id = ezcontentobject.contentclass_id
                  AND a1.id=eztags_attribute_link.objectattribute_id
                  AND eztags_attribute_link.keyword_id = eztags.id GROUP BY $sqlTarget ORDER BY {$sortingInfo['sortingFields']}";

        $keyWords = $db->arrayQuery( $query, $db_params );

        $trans = eZCharTransform::instance();

        foreach ( $keyWords as $keywordArray )
        {
            $keyword = $keywordArray['keyword'];
            $nodeID = $keywordArray['node_id'];
            $nodeObject = eZContentObjectTreeNode::fetch( $nodeID );

            if ( $nodeObject != null )
            {
                $keywordNodeArray[] = $nodeObject;
            }
        }
        return array( 'result' => $keywordNodeArray );
    }
}

?>
