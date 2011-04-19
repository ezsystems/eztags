<?php

eZExpiryHandler::registerShutdownFunction();

if ( !defined( 'MAX_AGE' ) )
{
    define( 'MAX_AGE', 86400 );
}

function washJS( $string )
{
    return str_replace( array( "\\", "/", "\n", "\t", "\r", "\b", "\f", '"' ), array( '\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"' ), $string );
}

function arrayToJSON( $array )
{
    if ( $array )
    {
        $result = array();
        $resultDict = array();
        $isDict = false;
        $index = 0;
        foreach ( $array as $key => $value )
        {
            if ( $key != $index++ )
            {
                $isDict = true;
            }

            if ( is_array( $value ) )
            {
                $value = arrayToJSON( $value );
            }
            else if ( !is_numeric( $value ) or $key == 'name' )
            {
                $value = '"' . washJS( $value ) . '"';
            }

            $result[] = $value;
            $resultDict[] = '"' . washJS( $key ) . '":' . $value;
        }
        if ( $isDict )
        {
            return '{' . implode( $resultDict, ',' ) . '}';
        }
        else
        {
            return '[' . implode( $result, ',' ) . ']';
        }
    }
    else
    {
        return '[]';
    }
}

function lookupIcon( $ini, $tag )
{
    $iconMap = $ini->variable( 'Icons', 'IconMap' );
    $returnValue = $ini->variable( 'Icons', 'Default' );

    if ( array_key_exists( $tag->ID, $iconMap ) && !empty( $iconMap[$tag->ID] ) )
    {
        $returnValue = $iconMap[$tag->ID];
    }
    else
    {
        $tempTag = $tag;
        while ( $tempTag->ParentID > 0 )
        {
            $tempTag = $tempTag->getParent();
            if ( array_key_exists( $tempTag->ID, $iconMap ) && !empty( $iconMap[$tempTag->ID] ) )
            {
                $returnValue = $iconMap[$tempTag->ID];
                break;
            }
        }
    }

    return eZURLOperator::eZImage( eZTemplate::factory(), 'tag_icons/small/' . $returnValue, '' );
}

while ( @ob_end_clean() );

if ( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) )
{
    header( $_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified' );
    header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + MAX_AGE ) . ' GMT' );
    header( 'Cache-Control: max-age=' . MAX_AGE );
    header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', strtotime( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) . ' GMT' );
    header( 'Pragma: ' );

    eZExecution::cleanExit();
}

$tagID = (int) $Params['TagID'];

$siteINI = eZINI::instance();
$eztagsINI = eZINI::instance( 'eztags.ini' );

$tag = eZTagsObject::fetch( $tagID );

if ( !( $tag instanceof eZTagsObject || $TagID == 0 ) )
{
    header( $_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found' );
}
else
{
    $children = eZTagsObject::fetchByParentID( $tagID );

    $response = array();
    $response['error_code']     = 0;
    $response['id']             = $tagID;
    $response['parent_id']      = ( $tag instanceof eZTagsObject ) ? $tag->ParentID : -1;
    $response['children_count'] = count( $children );
    $response['children']       = array();

    foreach ( $children as $child )
    {
        $childResponse = array();
        $childResponse['id']                        = $child->ID;
        $childResponse['parent_id']                 = $child->ParentID;
        $childResponse['has_children']              = ( eZTagsObject::childrenCountByParentID( $child->ID ) ) ? 1 : 0;
        $childResponse['synonyms_count']            = eZTagsObject::synonymsCount( $child->ID );
        $childResponse['subtree_limitations_count'] = $child->getSubTreeLimitationsCount();
        $childResponse['keyword']                   = $child->Keyword;
        $childResponse['url']                       = 'tags/id/' . $child->ID;
        $childResponse['icon']                      = lookupIcon( $eztagsINI, $child );

        eZURI::transformURI( $childResponse['url'] );
        $childResponse['modified']                  = $child->Modified;
        $response['children'][]                     = $childResponse;
    }
    $httpCharset = eZTextCodec::httpCharset();

    $jsonText = arrayToJSON( $response );

    $codec = eZTextCodec::instance( $httpCharset, 'unicode' );
    $jsonTextArray = $codec->convertString( $jsonText );
    $jsonText = '';
    foreach ( $jsonTextArray as $character )
    {
        if ( $character < 128 )
        {
            $jsonText .= chr( $character );
        }
        else
        {
            $jsonText .= '\u' . str_pad( dechex( $character ), 4, '0000', STR_PAD_LEFT );
        }
    }

    header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + MAX_AGE ) . ' GMT' );
    header( 'Cache-Control: cache, max-age=' . MAX_AGE . ', post-check=' . MAX_AGE . ', pre-check=' . MAX_AGE );
    header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', ( $tag instanceof eZTagsObject ) ? $tag->Modified : time() ) . ' GMT' );
    header( 'Pragma: cache' );
    header( 'Content-Type: application/json' );
    header( 'Content-Length: '. strlen( $jsonText ) );

    echo $jsonText;
}

eZExecution::cleanExit();

?>
