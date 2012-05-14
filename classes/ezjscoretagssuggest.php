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

        $searchString = $http->postVariable( 'search_string' );
        $subTreeLimit = $http->postVariable( 'subtree_limit' );
        $hideRootTag = $http->postVariable( 'hide_root_tag' ) == '1' ? true : false;

        $params = array( 'keyword' => array( 'like', $searchString . '%' ) );
        if ( $subTreeLimit > 0 )
        {
            if ( $hideRootTag )
            {
                $params['id'] = array( '<>', $subTreeLimit );
            }

            $params['path_string'] = array( 'like', '%/' . $subTreeLimit . '/%' );
        }
        $tags = eZTagsObject::fetchList( $params );

        $returnArray = array();
        $returnArray['status']  = 'success';
        $returnArray['message'] = '';
        $returnArray['tags']    = array();

        foreach ( $tags as $tag )
        {
            $returnArrayChild = array();
            $returnArrayChild['tag_parent_id']   = (int) $tag->attribute( 'parent_id' );
            $returnArrayChild['tag_parent_name'] = ( $tag->hasParent() ) ? $tag->getParent()->attribute( 'keyword' ) : '';
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
        $tags = array();
        $siteINI = eZINI::instance( 'site.ini' );

        $returnArray = array();
        $returnArray['status']  = 'success';
        $returnArray['message'] = '';
        $returnArray['tags']    = array();

        if ( $siteINI->variable( 'SearchSettings', 'SearchEngine' ) == 'ezsolr' && class_exists( 'eZSolr' ) )
        {
            $tagsCount = 1;
            $filteredTagsArray = array();
            $http = eZHTTPTool::instance();

            $tagsString = $http->postVariable( 'tags_string' );
            $tagsArray = explode( '|#', $tagsString );
            $subTreeLimit = $http->postVariable( 'subtree_limit' );
            $hideRootTag = $http->postVariable( 'hide_root_tag' ) == '1' ? true : false;

            if ( !empty( $tagsArray ) && strlen( trim( $tagsArray[0] ) ) > 0 )
            {
                $solrFilter = '"' . trim( $tagsArray[0] ) . '"';
                $filteredTagsArray[] = strtolower( trim( $tagsArray[0] ) );
                for ( $i = 1; $i < count( $tagsArray ); $i++ )
                {
                    if ( strlen( trim( $tagsArray[$i] ) ) > 0 )
                    {
                        $solrFilter = $solrFilter . ' OR "' . trim( $tagsArray[$i] ) . '"';
                        $filteredTagsArray[] = strtolower( trim( $tagsArray[$i] ) );
                        $tagsCount++;
                    }
                }
                $solrFilter = 'ezf_df_tags:(' . $solrFilter . ')';

                $solrSearch = new eZSolr();
                $params = array( 'SearchOffset'   => 0,
                                 'SearchLimit'    => 0,
                                 'Facet'          => array( array( 'field' => 'ezf_df_tags', 'limit' => 5 + $tagsCount, 'mincount', 1 ) ),
                                 'SortBy'         => null,
                                 'Filter'         => $solrFilter,
                                 'QueryHandler'   => 'ezpublish',
                                 'FieldsToReturn' => null );
                $searchResult = $solrSearch->search( '', $params );
                $facetResult = $searchResult['SearchExtras']->attribute( 'facet_fields' );

                $searchError = $searchResult['SearchExtras']->attribute( 'error' );
                if ( !empty( $searchError ) || !is_array( $facetResult ) || !is_array( $facetResult[0]['nameList'] ) )
                {
                    eZDebug::writeWarning( 'There was an error fetching tag suggestions from Solr. Maybe server is not running or using unpatched schema?', __METHOD__ );
                    return $returnArray;
                }

                $tags = array();
                foreach ( $facetResult[0]['nameList'] as $facetValue )
                {
                    if ( !in_array( strtolower( $facetValue ), $filteredTagsArray ) )
                    {
                        $tags[] = trim( $facetValue );
                    }
                }

                if ( !empty( $tags ) )
                {
                    $tags = eZTagsObject::fetchByKeyword( array( $tags ) );
                }
            }
        }

        foreach ( $tags as $tag )
        {
            if ( !$subTreeLimit > 0 || ( $subTreeLimit > 0 && strpos( $tag->attribute( 'path_string' ), '/' . $subTreeLimit . '/' ) !== false ) )
            {
                if ( !$hideRootTag || ( $hideRootTag && $tag->attribute( 'id' ) != $subTreeLimit ) )
                {
                    $returnArrayChild = array();
                    $returnArrayChild['tag_parent_id']   = (int) $tag->attribute( 'parent_id' );
                    $returnArrayChild['tag_parent_name'] = ( $tag->hasParent() ) ? $tag->getParent()->attribute( 'keyword' ) : '';
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
