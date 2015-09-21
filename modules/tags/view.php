<?php

/** @var eZModule $Module */
/** @var array $Params */

$http = eZHTTPTool::instance();
$keywordArray = $Params['Parameters'];
if ( !is_array( $keywordArray ) || empty( $keywordArray ) )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

$tag = eZTagsObject::fetchByUrl( $keywordArray );
if ( !$tag instanceof eZTagsObject )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

$viewParameters = array();
if ( isset( $Params['Offset'] ) )
    $viewParameters['offset'] = (int) $Params['Offset'];

$tpl = eZTemplate::factory();

$tpl->setVariable( 'tag', $tag );
$tpl->setVariable( 'view_parameters', $viewParameters );
$tpl->setVariable( 'show_reindex_message', false );

if ( $http->hasSessionVariable( 'eZTagsShowReindexMessage' ) )
{
    $http->removeSessionVariable( 'eZTagsShowReindexMessage' );
    $tpl->setVariable( 'show_reindex_message', true );
}

$Result = array();
$Result['content'] = $tpl->fetch( 'design:tags/view.tpl' );
$Result['path']    = eZTagsObject::generateModuleResultPath( $tag, true, false, false );
