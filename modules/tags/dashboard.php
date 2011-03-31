<?php

$http = eZHTTPTool::instance();

$contentInfoArray = array();

$tpl = eZTemplate::factory();

$tpl->setVariable('blocks', eZINI::instance( 'eztags.ini' )->variable( 'Dashboard', 'DashboardBlocks' ) );
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

$contentInfoArray['persistent_variable'] = false;
if ( $tpl->variable( 'persistent_variable' ) !== false )
    $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );

$Result['content_info'] = $contentInfoArray;

?>
