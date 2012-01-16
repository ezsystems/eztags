<?php

/**
 * ezjscoreTagsSuggest class implements ezjscore server functions for eztags
 *
 */
class ezjscoreTagsSuggest extends ezjscServerFunctions
{
    /**
     * Provides auto complete results when adding tags to object
     *
     * @static
     * @param mixed $args
     * @return array
     */
    public static function autocomplete( $args )
    {
        $http = eZHTTPTool::instance();
        $returnArray = array( 'status' => 'success', 'message' => '', 'tags' => array() );

        $searchString = $http->hasPostVariable( 'search_string' ) ? trim( $http->postVariable( 'search_string' ) ) : '';
        if ( empty( $searchString ) )
            return $returnArray;

        $subTreeLimit = $http->hasPostVariable( 'subtree_limit' ) ? (int) $http->postVariable( 'subtree_limit' ) : 0;
        $hideRootTag = $http->hasPostVariable( 'hide_root_tag' ) && $http->postVariable( 'hide_root_tag' ) == '1' ? true : false;

        $params = array( 'keyword' => array( 'like', $searchString . '%' ) );
        if ( $subTreeLimit > 0 )
        {
            if ( $hideRootTag )
                $params['id'] = array( '<>', $subTreeLimit );

            $params['path_string'] = array( 'like', '%/' . $subTreeLimit . '/%' );
        }

        $tags = eZTagsObject::fetchList( $params );
        if ( !is_array( $tags ) || empty( $tags ) )
            return $returnArray;

        foreach ( $tags as $tag )
        {
            $returnArrayChild = array();
            $returnArrayChild['tag_parent_id']   = (int) $tag->attribute( 'parent_id' );
            $returnArrayChild['tag_parent_name'] = $tag->hasParent( true ) ? $tag->getParent( true )->attribute( 'keyword' ) : '';
            $returnArrayChild['tag_name']        = $tag->attribute( 'keyword' );
            $returnArrayChild['tag_id']          = (int) $tag->attribute( 'id' );
            $returnArray['tags'][]               = $returnArrayChild;
        }

        return $returnArray;
    }

    /**
     * Provides suggestion results when adding tags to object
     *
     * @static
     * @param mixed $args
     * @return array
     */
    public static function suggest( $args )
    {
        $returnArray = array( 'status' => 'success', 'message' => '', 'tags' => array() );

        $searchEngine = eZINI::instance()->variable( 'SearchSettings', 'SearchEngine' );
        if ( !class_exists( 'eZSolr' ) || $searchEngine != 'ezsolr' )
            return $returnArray;

        $http = eZHTTPTool::instance();

        $tagIDs = $http->hasPostVariable( 'tag_ids' ) ? $http->postVariable( 'tag_ids' ) : '';
        if ( empty( $tagIDs ) )
            return $returnArray;

        $tagIDs = explode( '|#', $tagIDs );
        $tagIDs = array_values( array_unique( $tagIDs ) );

        $subTreeLimit = $http->hasPostVariable( 'subtree_limit' ) ? (int) $http->postVariable( 'subtree_limit' ) : 0;
        $hideRootTag = $http->hasPostVariable( 'hide_root_tag' ) && $http->postVariable( 'hide_root_tag' ) == '1' ? true : false;

        $solrSearch = new eZSolr();
        $params = array( 'SearchOffset'   => 0,
                         'SearchLimit'    => 0,
                         'Facet'          => array( array( 'field' => 'ezf_df_tag_ids', 'limit' => 5 + count( $tagIDs ), 'mincount' => 1 ) ),
                         'Filter'         => 'ezf_df_tag_ids:(' . implode( ' OR ', $tagIDs ) . ')',
                         'QueryHandler'   => 'ezpublish',
                         'AsObjects'       => false );

        $searchResult = $solrSearch->search( '', $params );
        if ( !isset( $searchResult['SearchExtras'] ) || !$searchResult['SearchExtras'] instanceof ezfSearchResultInfo )
            return $returnArray;

        $facetResult = $searchResult['SearchExtras']->attribute( 'facet_fields' );
        if ( !is_array( $facetResult ) || empty( $facetResult[0]['nameList'] ) )
            return $returnArray;

        $facetResult = $facetResult[0]['nameList'];
        $facetResult = array_values( $facetResult );

        $tagsToSuggest = array();
        foreach ( $facetResult as $result )
        {
            if ( !in_array( $result, $tagIDs ) )
                $tagsToSuggest[] = $result;
        }

        $tagsToSuggest = eZTagsObject::fetchList( array( 'id' => array( $tagsToSuggest ) ) );
        if ( !is_array( $tagsToSuggest ) || empty( $tagsToSuggest ) )
            return $returnArray;

        foreach ( $tagsToSuggest as $tag )
        {
            if ( !$subTreeLimit > 0 || ( $subTreeLimit > 0 && strpos( $tag->attribute( 'path_string' ), '/' . $subTreeLimit . '/' ) !== false ) )
            {
                if ( !$hideRootTag || ( $hideRootTag && $tag->attribute( 'id' ) != $subTreeLimit ) )
                {
                    $returnArrayChild = array();
                    $returnArrayChild['tag_parent_id']   = (int) $tag->attribute( 'parent_id' );
                    $returnArrayChild['tag_parent_name'] = $tag->hasParent( true ) ? $tag->getParent( true )->attribute( 'keyword' ) : '';
                    $returnArrayChild['tag_name']        = $tag->attribute( 'keyword' );
                    $returnArrayChild['tag_id']          = (int) $tag->attribute( 'id' );
                    $returnArray['tags'][]               = $returnArrayChild;
                }
            }
        }

        return $returnArray;
    }
}

?>
