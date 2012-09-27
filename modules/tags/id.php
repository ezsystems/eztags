<?php

/** @var eZModule $Module */
/** @var array $Params */

$tagID = (int) $Params['TagID'];
$locale = (string) $Params['Locale'];
$locale = !empty( $locale ) ? $locale : false;

$http = eZHTTPTool::instance();

$tag = eZTagsObject::fetch( $tagID, $locale );
if ( !$tag instanceof eZTagsObject )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

$viewParameters = array();
if ( isset( $Params['Offset'] ) )
    $viewParameters['offset'] = (int) $Params['Offset'];

if ( isset( $Params['Tab'] ) )
    $viewParameters['tab'] = trim( $Params['Tab'] );

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
$Result['path']    = eZTagsObject::generateModuleResultPath( $tag, false );
