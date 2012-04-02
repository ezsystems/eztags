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
        $returnArray = array( 'status' => 'success', 'message' => '', 'tags' => array() );

        $searchString = $http->hasPostVariable( 'search_string' ) ? trim( $http->postVariable( 'search_string' ) ) : '';
        if ( empty( $searchString ) )
            return $returnArray;

        $locale = $http->hasPostVariable( 'locale' ) ? $http->postVariable( 'locale' ) : '';
        if ( empty( $locale ) )
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

        $prioritizedLocales = self::getTopPrioritiziedLanguages( $locale );
        eZContentLanguage::setPrioritizedLanguages( $prioritizedLocales );

        $tags = eZTagsObject::fetchList( $params );

        eZContentLanguage::clearPrioritizedLanguages();

        if ( !is_array( $tags ) || empty( $tags ) )
            return $returnArray;

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
        $returnArray = array( 'status' => 'success', 'message' => '', 'tags' => array() );

        $searchEngine = eZINI::instance()->variable( 'SearchSettings', 'SearchEngine' );
        if ( !class_exists( 'eZSolr' ) || $searchEngine != 'ezsolr' )
            return $returnArray;

        $http = eZHTTPTool::instance();

        $tagIDs = $http->hasPostVariable( 'tag_ids' ) ? $http->postVariable( 'tag_ids' ) : '';
        if ( empty( $tagIDs ) )
            return $returnArray;

        $locale = $http->hasPostVariable( 'locale' ) ? $http->postVariable( 'locale' ) : '';
        if ( empty( $locale ) )
            return $returnArray;

        $tagIDs = explode( '|#', $tagIDs );
        $tagIDs = array_values( array_unique( $tagIDs ) );

        $subTreeLimit = $http->hasPostVariable( 'subtree_limit' ) ? (int) $http->postVariable( 'subtree_limit' ) : 0;
        $hideRootTag = $http->hasPostVariable( 'hide_root_tag' ) && $http->postVariable( 'hide_root_tag' ) == '1' ? true : false;

        $solrSearch = new eZSolr();
        $params = array( 'SearchOffset'   => 0,
                         'SearchLimit'    => 0,
                         'Facet'          => array( array( 'field' => 'ezf_df_tag_ids', 'limit' => 5 + count( $tagIDs ), 'mincount' => 1 ) ),
                         'Filter'         => array( 'ezf_df_tag_ids' => implode( ' OR ', $tagIDs ) ),
                         'QueryHandler'   => 'ezpublish',
                         'AsObjects'      => false );

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

        $prioritizedLocales = self::getTopPrioritiziedLanguages( $locale );
        eZContentLanguage::setPrioritizedLanguages( $prioritizedLocales );

        $tagsToSuggest = eZTagsObject::fetchList( array( 'id' => array( $tagsToSuggest ) ) );

        eZContentLanguage::clearPrioritizedLanguages();

        if ( !is_array( $tagsToSuggest ) || empty( $tagsToSuggest ) )
            return $returnArray;

        foreach ( $tagsToSuggest as $tag )
        {
            if ( !$subTreeLimit > 0 || ( $subTreeLimit > 0 && strpos( $tag->attribute( 'path_string' ), '/' . $subTreeLimit . '/' ) !== false ) )
            {
                if ( !$hideRootTag || ( $hideRootTag && $tag->attribute( 'id' ) != $subTreeLimit ) )
                {
                    $returnArrayChild = array();
                    $returnArrayChild['tag_parent_id']   = $tag->attribute( 'parent_id' );
                    $returnArrayChild['tag_parent_name'] = $tag->hasParent( true ) ? $tag->getParent( true )->attribute( 'keyword' ) : '';
                    $returnArrayChild['tag_name']        = $tag->attribute( 'keyword' );
                    $returnArrayChild['tag_id']          = $tag->attribute( 'id' );
                    $returnArrayChild['tag_locale']      = $tag->attribute( 'current_language' );
                    $returnArray['tags'][]               = $returnArrayChild;
                }
            }
        }

        return $returnArray;
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
        $returnArray = array( 'status' => 'success', 'message' => '', 'translations' => false );

        $http = eZHTTPTool::instance();

        $tagID = $http->hasPostVariable( 'tag_id' ) ? (int) $http->postVariable( 'tag_id' ) : 0;
        $tag = eZTagsObject::fetchWithMainTranslation( $tagID );
        if ( !$tag instanceof eZTagsObject )
            return $returnArray;

        $returnArray['translations'] = array();
        $tagTranslations = $tag->getTranslations();
        if ( !is_array( $tagTranslations ) || empty( $tagTranslations ) )
            return $returnArray;

        foreach ( $tagTranslations as $translation )
        {
            $returnArray['translations'][] = array(
                'locale'      => $translation->attribute( 'locale' ),
                'translation' => $translation->attribute( 'keyword' ) );
        }

        return $returnArray;
    }

    /**
     * Returns the current top prioritized languages without specified $locale
     *
     * @static
     *
     * @param string $locale
     *
     * @return array
     */
    static private function getTopPrioritiziedLanguages( $locale )
    {
        $prioritizedLocales = eZContentLanguage::prioritizedLanguageCodes();
        if ( !is_array( $prioritizedLocales ) )
        {
            $prioritizedLocales = array( $locale );
            return $prioritizedLocales;
        }

        $key = array_search( $locale, $prioritizedLocales );
        if ( $key !== false )
            unset( $prioritizedLocales[$key] );

        array_unshift( $prioritizedLocales, $locale );
        return $prioritizedLocales;
    }
}

?>
