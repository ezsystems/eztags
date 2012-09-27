<?php

/**
 * ezjscTags class implements eZ JS Core server functions for eztags
 */
class ezjscTags extends ezjscServerFunctions
{
    /**
     * Provides auto complete results when adding tags to object
     *
     * @static
     *
     * @param array $args
     *
     * @return array
     */
    static public function autocomplete( $args )
    {
        $http = eZHTTPTool::instance();

        $searchString = trim( $http->postVariable( 'search_string' ), '' );
        if ( empty( $searchString ) )
            return array( 'status' => 'success', 'message' => '', 'tags' => array() );

        return self::generateOutput(
            array( 'eztags_keyword.keyword' => array( 'like', $searchString . '%' ) ),
            $http->postVariable( 'subtree_limit', 0 ),
            $http->postVariable( 'hide_root_tag', '0' ),
            $http->postVariable( 'locale', '' )
        );
    }

    /**
     * Provides suggestion results when adding tags to object
     *
     * @static
     *
     * @param array $args
     *
     * @return array
     */
    static public function suggest( $args )
    {
        $http = eZHTTPTool::instance();

        $searchEngine = eZINI::instance()->variable( 'SearchSettings', 'SearchEngine' );
        if ( !class_exists( 'eZSolr' ) || $searchEngine != 'ezsolr' )
            return array( 'status' => 'success', 'message' => '', 'tags' => array() );

        $tagIDs = $http->postVariable( 'tag_ids', '' );
        if ( empty( $tagIDs ) )
            return array( 'status' => 'success', 'message' => '', 'tags' => array() );

        $tagIDs = array_values( array_unique( explode( '|#', $tagIDs ) ) );

        $solrSearch = new eZSolr();
        $params = array(
            'SearchOffset'   => 0,
            'SearchLimit'    => 0,
            'Facet'          => array(
                array(
                    'field' => 'ezf_df_tag_ids',
                    'limit' => 5 + count( $tagIDs ),
                    'mincount' => 1
                )
            ),
            'Filter'         => array(
                'ezf_df_tag_ids' => implode( ' OR ', $tagIDs )
            ),
            'QueryHandler'   => 'ezpublish',
            'AsObjects'      => false
        );

        $searchResult = $solrSearch->search( '', $params );
        if ( !isset( $searchResult['SearchExtras'] ) || !$searchResult['SearchExtras'] instanceof ezfSearchResultInfo )
        {
            eZDebug::writeWarning( 'There was an error fetching tag suggestions from Solr. Maybe server is not running or using unpatched schema?', __METHOD__ );
            return array( 'status' => 'success', 'message' => '', 'tags' => array() );
        }

        $facetResult = $searchResult['SearchExtras']->attribute( 'facet_fields' );
        if ( !is_array( $facetResult ) || !is_array( $facetResult[0]['nameList'] ) )
        {
            eZDebug::writeWarning( 'There was an error fetching tag suggestions from Solr. Maybe server is not running or using unpatched schema?', __METHOD__ );
            return array( 'status' => 'success', 'message' => '', 'tags' => array() );
        }

        $facetResult = array_values( $facetResult[0]['nameList'] );

        $tagsToSuggest = array();
        foreach ( $facetResult as $result )
        {
            if ( !in_array( $result, $tagIDs ) )
                $tagsToSuggest[] = $result;
        }

        if ( empty( $tagsToSuggest ) )
            return array( 'status' => 'success', 'message' => '', 'tags' => array() );

        return self::generateOutput(
            array( 'id' => array( $tagsToSuggest ) ),
            0,
            false,
            $http->postVariable( 'locale', '' )
        );
    }

    /**
     * Returns requested tag translations
     *
     * @static
     *
     * @param array $args
     *
     * @return array
     */
    static public function tagtranslations( $args )
    {
        $returnArray = array(
            'status' => 'success',
            'message' => '',
            'translations' => false
        );

        $http = eZHTTPTool::instance();

        $tagID = (int) $http->postVariable( 'tag_id', 0 );
        $tag = eZTagsObject::fetchWithMainTranslation( $tagID );
        if ( !$tag instanceof eZTagsObject )
            return $returnArray;

        $returnArray['translations'] = array();

        /** @var eZTagsKeyword[] $tagTranslations */
        $tagTranslations = $tag->getTranslations();
        if ( !is_array( $tagTranslations ) || empty( $tagTranslations ) )
            return $returnArray;

        foreach ( $tagTranslations as $translation )
        {
            $returnArray['translations'][] = array(
                'locale'      => $translation->attribute( 'locale' ),
                'translation' => $translation->attribute( 'keyword' )
            );
        }

        return $returnArray;
    }

    /**
     * Generates output for use with autocomplete and suggest methods
     *
     * @static
     *
     * @param array $params
     * @param int $subTreeLimit
     * @param bool $hideRootTag
     * @param string $locale
     *
     * @return array
     */
    static private function generateOutput( array $params, $subTreeLimit, $hideRootTag, $locale )
    {
        $subTreeLimit = (int) $subTreeLimit;
        $hideRootTag = (bool) $hideRootTag;
        $locale = (string) $locale;

        if ( empty( $locale ) )
             return array( 'status' => 'success', 'message' => '', 'tags' => array() );

        // @TODO Fix synonyms not showing up in autocomplete
        // when subtree limit is defined in class attribute
        if ( $subTreeLimit > 0 )
        {
            if ( $hideRootTag )
                $params['id'] = array( '<>', $subTreeLimit );

            $params['path_string'] = array( 'like', '%/' . $subTreeLimit . '/%' );
        }

        // first fetch tags that exist in selected locale
        /** @var eZTagsObject[] $tags */
        $tags = eZTagsObject::fetchList( $params, null, null, false, $locale );
        if ( !is_array( $tags ) )
            $tags = array();

        $tagsIDsToExclude = array_map(
            function ( $tag )
            {
                /** @var eZTagsObject $tag */
                return (int) $tag->attribute( 'id' );
            },
            $tags
        );

        // then fetch the rest of tags, but exclude already fetched ones
        // fetch with main translation to be consistent with eztags attribute content

        $customConds = eZTagsObject::fetchCustomCondsSQL( $params, true );
        if ( !empty( $tagsIDsToExclude ) )
            $customConds .= " AND " . eZDB::instance()->generateSQLINStatement( $tagsIDsToExclude, 'eztags.id', true, true, 'int' ) . " ";

        $tagsRest = eZPersistentObject::fetchObjectList(
            eZTagsObject::definition(), array(), $params,
            null, null, true, false,
            array(
                'DISTINCT eztags.*',
                array(
                    'operation' => 'eztags_keyword.keyword',
                    'name'      => 'keyword'
                ),
                array(
                    'operation' => 'eztags_keyword.locale',
                    'name'      => 'locale'
                )
            ),
            array( 'eztags_keyword' ),
            $customConds
        );

        if ( !is_array( $tagsRest ) )
            $tagsRest = array();

        // finally, return both set of tags as one list

        $tags = array_merge( $tags, $tagsRest );

        $returnArray = array(
            'status' => 'success',
            'message' => '',
            'tags' => array()
        );

        foreach ( $tags as $tag )
        {
            $returnArrayChild = array();
            $returnArrayChild['tag_parent_id']   = $tag->attribute( 'parent_id' );
            $returnArrayChild['tag_parent_name'] = $tag->hasParent( true ) ? $tag->getParent( true )->attribute( 'keyword' ) : '';
            $returnArrayChild['tag_name']        = $tag->attribute( 'keyword' );
            $returnArrayChild['tag_id']          = $tag->attribute( 'id' );
            $returnArrayChild['tag_locale']      = $tag->attribute( 'current_language' );
            $returnArray['tags'][]               = $returnArrayChild;
        }

        return $returnArray;
    }
}
