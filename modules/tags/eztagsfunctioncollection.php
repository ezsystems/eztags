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
}

?>
