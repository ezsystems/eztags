<?php

/**
 * eZTagsFunctionCollection class implements fetch functions for eztags
 *
 */
class eZTagsFunctionCollection
{
    /**
     * Fetches eZTagsObject object for the provided tag ID
     *
     * @static
     * @param integer $tag_id
     * @return array
     */
    static public function fetchTag( $tag_id )
    {
        $result = eZTagsObject::fetch( $tag_id );

        if( $result instanceof eZTagsObject )
            return array( 'result' => $result );
        else
            return array( 'result' => false );
    }

    /**
     * Fetches all tags named with provided keyword
     *
     * @static
     * @param string $keyword
     * @return array
     */
    static public function fetchTagsByKeyword( $keyword )
    {
        $result = eZTagsObject::fetchByKeyword( $keyword );

        if( is_array( $result ) && !empty( $result ) )
            return array( 'result' => $result );
        else
            return array( 'result' => false );
    }

    /**
     * Fetches tag identified with provided remote_id
     *
     * @static
     * @param string $remote_id
     * @return array
     */
    static public function fetchTagByRemoteID( $remote_id )
    {
        $result = eZTagsObject::fetchByRemoteID( $remote_id );

        if( $result instanceof eZTagsObject )
            return array( 'result' => $result );
        else
            return array( 'result' => false );
    }

    /**
     * Fetches subtree of tags by specified parameters
     *
     * @static
     * @param integer $parentTagID
     * @param array $sortBy
     * @param integer $offset
     * @param integer $limit
     * @param integer $depth
     * @param string $depthOperator
     * @param bool $includeSynonyms
     * @return array
     */
    static public function fetchTagTree( $parentTagID, $sortBy, $offset, $limit, $depth, $depthOperator, $includeSynonyms )
    {
        if ( !is_numeric( $parentTagID ) || (int) $parentTagID < 0 )
            return array( 'result' => false );

        $params = array( 'SortBy' => $sortBy,
                         'Offset' => $offset,
                         'Limit'  => $limit,
                         'IncludeSynonyms' => $includeSynonyms );

        if ( $depth !== false )
        {
            $params['Depth'] = $depth;
            $params['DepthOperator'] = $depthOperator;
        }

        $tags = eZTagsObject::subTreeByTagID( $params, $parentTagID );

        return array( 'result' => $tags );
    }

    /**
     * Fetches subtree tag count by specified parameters
     *
     * @static
     * @param integer $parentTagID
     * @param integer $depth
     * @param string $depthOperator
     * @param bool $includeSynonyms
     * @return integer
     */
    static public function fetchTagTreeCount( $parentTagID, $depth, $depthOperator, $includeSynonyms )
    {
        if ( !is_numeric( $parentTagID ) || (int) $parentTagID < 0 )
            return array( 'result' => 0 );

        $params = array( 'IncludeSynonyms' => $includeSynonyms );

        if ( $depth !== false )
        {
            $params['Depth'] = $depth;
            $params['DepthOperator'] = $depthOperator;
        }

        $tagsCount = eZTagsObject::subTreeCountByTagID( $params, $parentTagID );

        return array( 'result' => $tagsCount );
    }

    /**
     * Fetches latest modified tags by specified parameters
     *
     * @static
     * @param integer $parentTagID
     * @param integer $limit
     * @return array
     */
    static public function fetchLatestTags( $parentTagID = false, $limit = 0 )
    {
        $filterArray = array( 'main_tag_id' => 0 );

        if ( $parentTagID !== false )
            $filterArray['parent_id'] = (int) $parentTagID;

        $result = eZPersistentObject::fetchObjectList( eZTagsObject::definition(), null,
                                                       $filterArray,
                                                       array( 'modified' => 'desc' ),
                                                       array( 'offset' => 0, 'limit' => $limit ) );

        if ( is_array( $result ) && !empty( $result ) )
            return array( 'result' => $result );
        else
            return array( 'result' => false );
    }
}

?>
