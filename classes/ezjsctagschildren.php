<?php

/**
 * ezjscTagsChildren class implements eZ JS Core server functions for eztags children list
 */
class ezjscTagsChildren extends ezjscServerFunctions
{
    /**
     * Returns the JSON encoded string of children tags for supplied GET params
     * Used in YUI version of children tags list in admin interface
     *
     * @static
     *
     * @param array $args
     *
     * @return string
     */
    static public function tagsChildren( $args )
    {
        $http = eZHTTPTool::instance();
        $filter = urldecode( trim( $http->getVariable( 'filter', '' ) ) );

        if ( !isset( $args[0] ) || !is_numeric( $args[0] ) )
            return array( 'count' => 0, 'offset' => false, 'filter' => $filter, 'data' => array() );

        $offset = false;
        $limits = null;

        if ( $http->hasGetVariable( 'offset' ) )
        {
            $offset = (int) $http->getVariable( 'offset' );

            if ( $http->hasGetVariable( 'limit' ) )
                $limit = (int) $http->getVariable( 'limit' );
            else
                $limit = 10;

            $limits = array( 'offset' => $offset, 'limit' => $limit );
        }

        $sorts = null;
        if ( $http->hasGetVariable( 'sortby' ) )
        {
            $sortBy = trim( $http->getVariable( 'sortby' ) );

            $sortDirection = 'asc';
            if ( $http->hasGetVariable( 'sortdirection' ) && trim( $http->getVariable( 'sortdirection' ) ) == 'desc' )
                $sortDirection = 'desc';

            $sorts = array( $sortBy => $sortDirection );
        }

        $fetchParams = array( 'parent_id' => (int) $args[0], 'main_tag_id' => 0 );
        if ( !empty( $filter ) )
            $fetchParams['keyword'] = array( 'like', '%' . $filter . '%' );

        /** @var eZTagsObject[] $children */
        $children = eZTagsObject::fetchList( $fetchParams, $limits, $sorts );
        $childrenCount = eZTagsObject::fetchListCount( $fetchParams );

        if ( !is_array( $children ) || empty( $children ) )
            return array( 'count' => 0, 'offset' => false, 'filter' => $filter, 'data' => array() );

        $dataArray = array();
        foreach ( $children as $child )
        {
            $tagArray = array();
            $tagArray['id'] = $child->attribute( 'id' );
            $tagArray['keyword'] = htmlspecialchars( $child->attribute( 'keyword' ), ENT_QUOTES );
            $tagArray['modified'] = $child->attribute( 'modified' );

            $tagArray['translations'] = array();
            foreach ( $child->getTranslations() as $translation )
            {
                $tagArray['translations'][] = htmlspecialchars( $translation->attribute( 'locale' ), ENT_QUOTES );
            }

            $dataArray[] = $tagArray;
        }

        return array(
            'count'  => $childrenCount,
            'offset' => $offset,
            'filter' => $filter,
            'data'   => $dataArray
        );
    }
}
