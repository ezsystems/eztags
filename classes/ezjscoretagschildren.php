<?php

/**
 * ezjscoreTagsChildren class implements eZ JS Core server functions for eztags children list
 */
class ezjscoreTagsChildren extends ezjscServerFunctions
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
    public static function tagsChildren( $args )
    {
        if ( !isset( $args[0] ) || !is_numeric( $args[0] ) )
            return json_encode( array( 'count' => 0, 'offset' => false, 'data' => array() ) );

        $http = eZHTTPTool::instance();

        $offset = false;
        $limit = false;
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

        $children = eZTagsObject::fetchList(
            array( 'parent_id' => (int) $args[0], 'main_tag_id' => 0 ),
            $limits, $sorts );

        $childrenCount = eZTagsObject::fetchListCount(
            array( 'parent_id' => (int) $args[0], 'main_tag_id' => 0 ) );

        if ( !is_array( $children ) || empty( $children ) )
            return json_encode( array( 'count' => 0, 'offset' => false, 'data' => array() ) );

        $dataArray = array();
        foreach ( $children as $child )
        {
            $tagArray = array();
            $tagArray['id'] = $child->attribute( 'id' );
            $tagArray['keyword'] = $child->attribute( 'keyword' );
            $tagArray['modified'] = $child->attribute( 'modified' );

            $tagArray['translations'] = array();
            foreach ( $child->getTranslations() as $translation )
            {
                $tagArray['translations'][] = $translation->attribute( 'locale' );
            }

            $dataArray[] = $tagArray;
        }

        return json_encode( array( 'count' => $childrenCount, 'offset' => $offset, 'data' => $dataArray ) );
    }
}

?>
