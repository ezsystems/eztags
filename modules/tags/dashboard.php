<?php

$http = eZHTTPTool::instance();

$viewParameters = array();
if ( isset( $Params['Offset'] ) )
    $viewParameters['offset'] = (int) $Params['Offset'];

if ( isset( $Params['Tab'] ) )
    $viewParameters['tab'] = trim( $Params['Tab'] );

$tpl = eZTemplate::factory();

$tpl->setVariable( 'view_parameters', $viewParameters );
$tpl->setVariable( 'show_reindex_message', false );

if ( $http->hasSessionVariable( 'eZTagsShowReindexMessage' ) )
{
    $http->removeSessionVariable( 'eZTagsShowReindexMessage' );
    $tpl->setVariable( 'show_reindex_message', true );
}

$Result = array();
$Result['content'] = $tpl->fetch( 'design:tags/dashboard.tpl' );
$Result['path']    = eZTagsObject::generateModuleResultPath( false, null,
                                                             ezpI18n::tr( 'extension/eztags/tags/view', 'Tags dashboard' ) );
