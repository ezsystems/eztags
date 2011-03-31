<?php

$tagID = (int) $Params['TagID'];
$http = eZHTTPTool::instance();

if ( $tagID <= 0 )
{
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

$tag = eZTagsObject::fetch( $tagID );
if ( !($tag instanceof eZTagsObject ) )
{
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

$tpl = eZTemplate::factory();

$tpl->setVariable( 'blocks', eZINI::instance( 'eztags.ini' )->variable( 'View', 'ViewBlocks' ) );
$tpl->setVariable( 'tag', $tag );
$tpl->setVariable( 'persistent_variable', false );
$tpl->setVariable( 'show_reindex_message', false );

if ( $http->hasSessionVariable( 'eZTagsShowReindexMessage' ) )
{
    $http->removeSessionVariable( 'eZTagsShowReindexMessage' );
    $tpl->setVariable( 'show_reindex_message', true );
}

$Result = array();
$Result['content'] = $tpl->fetch( 'design:tags/view.tpl' );
$Result['path']    = array();

$tempTag = $tag;
while ( $tempTag->hasParent() )
{
    $tempTag = $tempTag->getParent();
    $Result['path'][] = array( 'tag_id' => $tempTag->ID,
                               'text'   => $tempTag->Keyword,
                               'url'    => 'tags/id/' . $tempTag->ID );
}

$Result['path'] = array_reverse( $Result['path'] );
$Result['path'][] = array( 'tag_id' => $tag->ID,
                           'text'   => $tag->Keyword,
                           'url'    => false );

$contentInfoArray = array();
$contentInfoArray['persistent_variable'] = false;
if ( $tpl->variable( 'persistent_variable' ) !== false )
    $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );

$Result['content_info'] = $contentInfoArray;

?>
