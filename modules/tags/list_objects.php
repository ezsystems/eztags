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

$tpl = eZTemplate::factory();

$tpl->setVariable( 'tag', $tag );
$tpl->setVariable( 'view_parameters', $viewParameters );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:tags/list_objects.tpl' );
$Result['path']    = eZTagsObject::generateModuleResultPath( $tag, false );
