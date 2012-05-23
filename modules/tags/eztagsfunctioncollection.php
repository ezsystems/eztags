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
    static public function fetchTag( $tag_id, $ignoreVisibility )
    {
        $result = eZTagsObject::fetch( $tag_id, $ignoreVisibility );

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
    static public function fetchTagsByKeyword( $keyword, $ignoreVisibility )
    {
        $result = eZTagsObject::fetchByKeyword( $keyword, $ignoreVisibility );

        if( is_array( $result ) && !empty( $result ) )
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
    static public function fetchTagTree( $parentTagID, $sortBy, $offset, $limit, $depth, $depthOperator, $includeSynonyms, $ignoreVisibility )
    {
        if ( !is_numeric( $parentTagID ) || (int) $parentTagID < 0 )
            return array( 'result' => false );

        $showHidden = eZTagsObject::showHiddenTagsEnabled();

        $params = array( 'SortBy' => $sortBy,
                         'Offset' => $offset,
                         'Limit'  => $limit,
                         'IncludeSynonyms' => $includeSynonyms );

        if( $ignoreVisibility !== null )
        {
            $params['IgnoreVisibility'] = $ignoreVisibility;
        }

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
    static public function fetchTagTreeCount( $parentTagID, $depth, $depthOperator, $includeSynonyms, $ignoreVisibility )
    {
        if ( !is_numeric( $parentTagID ) || (int) $parentTagID < 0 )
            return array( 'result' => 0 );

        $showHidden = eZTagsObject::showHiddenTagsEnabled();
        $params = array( 'IncludeSynonyms' => $includeSynonyms );

        if( $ignoreVisibility !== null )
        {
            $params['IgnoreVisibility'] = $ignoreVisibility;
        }

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
    static public function fetchLatestTags( $parentTagID = false, $limit = 0, $ignoreVisibility )
    {
        $filterArray = array( 'main_tag_id' => 0 );

        if ( $parentTagID !== false )
            $filterArray['parent_id'] = (int) $parentTagID;

        if( $ignoreVisibility === null )
        {
            $ignoreVisibility = eZTagsObject::showHiddenTagsEnabled();
        }

        if( !$ignoreVisibility )
            $filterArray['hidden'] = 0;

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
