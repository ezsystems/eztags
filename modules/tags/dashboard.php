<?php

$http = eZHTTPTool::instance();

$viewParameters = array();
if ( isset( $Params['Offset'] ) )
    $viewParameters['offset'] = (int) $Params['Offset'];

$tpl = eZTemplate::factory();

$tpl->setVariable('blocks', eZINI::instance( 'eztags.ini' )->variable( 'Dashboard', 'DashboardBlocks' ) );
$tpl->setVariable( 'view_parameters', $viewParameters );
$tpl->setVariable( 'persistent_variable', false );
$tpl->setVariable( 'show_reindex_message', false );

if ( $http->hasSessionVariable( 'eZTagsShowReindexMessage' ) )
{
    $http->removeSessionVariable( 'eZTagsShowReindexMessage' );
    $tpl->setVariable( 'show_reindex_message', true );
}

$Result = array();
$Result['content'] = $tpl->fetch( 'design:tags/dashboard.tpl' );
$Result['path']    = array( array( 'tag_id' => 0,
                                   'text'   => ezpI18n::tr( 'extension/eztags/tags/view', 'Tags Dashboard' ),
                                   'url'    => false ) );

$contentInfoArray = array();
$contentInfoArray['persistent_variable'] = false;
if ( $tpl->variable( 'persistent_variable' ) !== false )
    $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );

$Result['content_info'] = $contentInfoArray;

?>
