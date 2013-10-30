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
     *
     * @param int $tagID
     * @param mixed $language
     *
     * @return array
     */
    static public function fetchTag( $tagID, $language = false )
    {
        if ( $language )
        {
            if ( !is_array( $language ) )
                $language = array( $language );
            eZContentLanguage::setPrioritizedLanguages( $language );
        }

        $result = false;
        if ( is_numeric( $tagID ) )
        {
            $result = eZTagsObject::fetch( $tagID );
        }
        else if ( is_array( $tagID ) && !empty( $tagID ) )
        {
            $result = eZTagsObject::fetchList( array( 'id' => array( $tagID ) ) );
        }

        if ( $language )
            eZContentLanguage::clearPrioritizedLanguages();

        if ( $result instanceof eZTagsObject || ( is_array( $result ) && !empty( $result ) ) )
            return array( 'result' => $result );

        return array( 'result' => false );
    }

    /**
     * Fetches all tags named with provided keyword
     *
     * @static
     *
     * @param string $keyword
     * @param mixed $language
     *
     * @return array
     */
    static public function fetchTagsByKeyword( $keyword, $language = false )
    {
        if ( $language )
        {
            if ( !is_array( $language ) )
                $language = array( $language );
            eZContentLanguage::setPrioritizedLanguages( $language );
        }

        if ( strpos( $keyword, '*' ) !== false )
        {
            $keyword = preg_replace(
                array(
                    '#%#m',
                    '#(?<!\\\\)\\*#m',
                    '#(?<!\\\\)\\\\\\*#m',
                    '#\\\\\\\\#m'
                ),
                array(
                    '\\%',
                    '%',
                    '*',
                    '\\\\'
                ),
                $keyword
            );

            $keyword = array( 'like', $keyword );
        }

        $result = eZTagsObject::fetchByKeyword( $keyword );

        if ( $language )
            eZContentLanguage::clearPrioritizedLanguages();

        if ( is_array( $result ) && !empty( $result ) )
            return array( 'result' => $result );

        return array( 'result' => false );
    }

    /**
     * Fetches tag identified with provided remote ID
     *
     * @static
     *
     * @param string $remoteID
     * @param mixed $language
     *
     * @return array
     */
    static public function fetchTagByRemoteID( $remoteID, $language = false )
    {
        if ( $language )
        {
            if ( !is_array( $language ) )
                $language = array( $language );
            eZContentLanguage::setPrioritizedLanguages( $language );
        }

        $result = eZTagsObject::fetchByRemoteID( $remoteID );

        if ( $language )
            eZContentLanguage::clearPrioritizedLanguages();

        if ( $result instanceof eZTagsObject )
            return array( 'result' => $result );

        return array( 'result' => false );
    }

    /**
     * Fetches tag identified with provided URL
     *
     * @static
     *
     * @param string $url
     * @param mixed $language
     *
     * @return array
     */
    static public function fetchTagByUrl( $url, $language = false )
    {
        if ( $language )
        {
            if ( !is_array( $language ) )
                $language = array( $language );
            eZContentLanguage::setPrioritizedLanguages( $language );
        }

        $result = eZTagsObject::fetchByUrl( $url );

        if ( $language )
            eZContentLanguage::clearPrioritizedLanguages();

        if ( $result instanceof eZTagsObject )
            return array( 'result' => $result );

        return array( 'result' => false );
    }

    /**
     * Fetches subtree of tags by specified parameters
     *
     * @static
     *
     * @param int $parentTagID
     * @param array $sortBy
     * @param int $offset
     * @param int $limit
     * @param int $depth
     * @param string $depthOperator
     * @param bool $includeSynonyms
     * @param mixed $language
     *
     * @return array
     */
    static public function fetchTagTree( $parentTagID, $sortBy, $offset, $limit, $depth, $depthOperator, $includeSynonyms, $language = false )
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

        if ( $language )
        {
            if ( !is_array( $language ) )
                $language = array( $language );
            eZContentLanguage::setPrioritizedLanguages( $language );
        }

        $tags = eZTagsObject::subTreeByTagID( $params, $parentTagID );

        if ( $language )
            eZContentLanguage::clearPrioritizedLanguages();

        return array( 'result' => $tags );
    }

    /**
     * Fetches subtree tag count by specified parameters
     *
     * @static
     *
     * @param int $parentTagID
     * @param int $depth
     * @param string $depthOperator
     * @param bool $includeSynonyms
     * @param mixed $language
     *
     * @return array
     */
    static public function fetchTagTreeCount( $parentTagID, $depth, $depthOperator, $includeSynonyms, $language = false )
    {
        if ( !is_numeric( $parentTagID ) || (int) $parentTagID < 0 )
            return array( 'result' => 0 );

        $params = array( 'IncludeSynonyms' => $includeSynonyms );

        if ( $depth !== false )
        {
            $params['Depth'] = $depth;
            $params['DepthOperator'] = $depthOperator;
        }

        if ( $language )
        {
            if ( !is_array( $language ) )
                $language = array( $language );
            eZContentLanguage::setPrioritizedLanguages( $language );
        }

        $tagsCount = eZTagsObject::subTreeCountByTagID( $params, $parentTagID );

        if ( $language )
            eZContentLanguage::clearPrioritizedLanguages();

        return array( 'result' => $tagsCount );
    }

    /**
     * Fetches latest modified tags by specified parameters
     *
     * @static
     *
     * @param int|bool $parentTagID
     * @param int $limit
     * @param mixed $language
     *
     * @return array
     */
    static public function fetchLatestTags( $parentTagID = false, $limit = 0, $language = false )
    {
        $parentTagID = (int) $parentTagID;

        $filterArray = array();
        $filterArray['main_tag_id'] = 0;
        $filterArray['id'] = array( '!=', $parentTagID );

        if ( $parentTagID > 0 )
            $filterArray['path_string'] = array( 'like', '%/' . $parentTagID . '/%' );

        if ( $language )
        {
            if ( !is_array( $language ) )
                $language = array( $language );
            eZContentLanguage::setPrioritizedLanguages( $language );
        }

        $result = eZTagsObject::fetchList( $filterArray,
                                           array( 'offset' => 0, 'limit' => $limit ),
                                           array( 'modified' => 'desc' ) );

        if ( $language )
            eZContentLanguage::clearPrioritizedLanguages();

        if ( is_array( $result ) && !empty( $result ) )
            return array( 'result' => $result );

        return array( 'result' => false );
    }
}
