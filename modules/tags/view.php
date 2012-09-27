<?php

/** @var eZModule $Module */
/** @var array $Params */

$http = eZHTTPTool::instance();
$keywordArray = $Params['Parameters'];

if ( !is_array( $keywordArray ) || empty( $keywordArray ) )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

$parentID = 0;
for ( $i = 0; $i < count( $keywordArray ) - 1; $i++ )
{
    /** @var eZTagsObject[] $tags */
    $tags = eZTagsObject::fetchList( array( 'parent_id' => $parentID, 'main_tag_id' => 0, 'keyword' => urldecode( trim( $keywordArray[$i] ) ) ) );
    if ( !is_array( $tags ) || empty( $tags ) )
        return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

    $parentID = $tags[0]->attribute( 'id' );
}

$tags = eZTagsObject::fetchList( array( 'parent_id' => $parentID, 'keyword' => urldecode( trim( $keywordArray[count( $keywordArray ) - 1] ) ) ) );
if ( !is_array( $tags ) || empty( $tags ) )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

$viewParameters = array();
if ( isset( $Params['Offset'] ) )
    $viewParameters['offset'] = (int) $Params['Offset'];

$tpl = eZTemplate::factory();

$tpl->setVariable( 'tag', $tags[0] );
$tpl->setVariable( 'view_parameters', $viewParameters );
$tpl->setVariable( 'show_reindex_message', false );

if ( $http->hasSessionVariable( 'eZTagsShowReindexMessage' ) )
{
    $http->removeSessionVariable( 'eZTagsShowReindexMessage' );
    $tpl->setVariable( 'show_reindex_message', true );
}

$Result = array();
$Result['content'] = $tpl->fetch( 'design:tags/view.tpl' );
$Result['path']    = eZTagsObject::generateModuleResultPath( $tags[0], true, false, false );
