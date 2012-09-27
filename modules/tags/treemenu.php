<?php

/** @var array $Params */

eZExpiryHandler::registerShutdownFunction();

if ( !defined( 'MAX_AGE' ) )
{
    define( 'MAX_AGE', 86400 );
}

for ( $i = 0, $obLevel = ob_get_level(); $i < $obLevel; ++$i )
{
    ob_end_clean();
}

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
if ( $tagID > 0 && !$tag instanceof eZTagsObject )
{
    header( $_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found' );
    eZExecution::cleanExit();
}

$maxTags = 100;
if ( $eztagsINI->hasVariable( 'TreeMenu', 'MaxTags' ) )
{
    $iniMaxTags = $eztagsINI->variable( 'TreeMenu', 'MaxTags' );
    if ( is_numeric( $iniMaxTags ) )
        $maxTags = (int) $iniMaxTags;
}

$limitArray = null;
if ( $maxTags > 0 )
    $limitArray = array( 'offset' => 0, 'length' => $maxTags );

$children = eZTagsObject::fetchList( array( 'parent_id'   => $tagID,
                                            'main_tag_id' => 0 ),
                                     $limitArray );

$response = array();
$response['error_code']     = 0;
$response['id']             = $tagID;
$response['parent_id']      = $tag instanceof eZTagsObject ? (int) $tag->attribute( 'parent_id' ) : -1;
$response['children_count'] = count( $children );
$response['children']       = array();

foreach ( $children as $child )
{
    $childResponse = array();
    $childResponse['id']                        = (int) $child->attribute( 'id' );
    $childResponse['parent_id']                 = (int) $child->attribute( 'parent_id' );
    $childResponse['has_children']              = $child->getChildrenCount() > 0 ? 1 : 0;
    $childResponse['synonyms_count']            = $child->getSynonymsCount();
    $childResponse['subtree_limitations_count'] = $child->getSubTreeLimitationsCount();
    $childResponse['language_name_array']       = $child->languageNameArray();
    $childResponse['keyword']                   = $child->attribute( 'keyword' );
    $childResponse['url']                       = 'tags/id/' . $child->attribute( 'id' );
    $childResponse['icon']                      = eZTagsTemplateFunctions::getTagIcon( $child->getIcon() );

    eZURI::transformURI( $childResponse['url'] );
    $childResponse['modified']                  = (int) $child->attribute( 'modified' );
    $response['children'][]                     = $childResponse;
}

$jsonText = json_encode( $response );

header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + MAX_AGE ) . ' GMT' );
header( 'Cache-Control: cache, max-age=' . MAX_AGE . ', post-check=' . MAX_AGE . ', pre-check=' . MAX_AGE );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $tag instanceof eZTagsObject ? (int) $tag->attribute( 'modified' ) : time() ) . ' GMT' );
header( 'Pragma: cache' );
header( 'Content-Type: application/json' );
header( 'Content-Length: '. strlen( $jsonText ) );

echo $jsonText;
eZExecution::cleanExit();
