<?php

$http = eZHTTPTool::instance();
$keywordArray = $Params['Parameters'];

if ( !( is_array( $keywordArray ) && !empty( $keywordArray ) ) )
{
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

$parentID = 0;
for ( $i = 0; $i < count( $keywordArray ) - 1; $i++ )
{
    $tags = eZTagsObject::fetchList( array( 'parent_id' => $parentID, 'main_tag_id' => 0, 'keyword' => urldecode( trim( $keywordArray[$i] ) ) ) );
    if ( is_array( $tags ) && !empty( $tags ) )
    {
        $parentID = $tags[0]->attribute( 'id' );
    }
    else
    {
        return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
    }
}

$tags = eZTagsObject::fetchList( array( 'parent_id' => $parentID, 'keyword' => urldecode( trim( $keywordArray[count( $keywordArray ) - 1] ) ) ) );
if ( !( is_array( $tags ) && !empty( $tags ) ) )
{
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

$viewParameters = array();
if ( isset( $Params['Offset'] ) )
    $viewParameters['offset'] = (int) $Params['Offset'];

$tpl = eZTemplate::factory();

$tpl->setVariable( 'blocks', eZINI::instance( 'eztags.ini' )->variable( 'View', 'ViewBlocks' ) );
$tpl->setVariable( 'tag', $tags[0] );
$tpl->setVariable( 'view_parameters', $viewParameters );
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

$tempTag = $tags[0];
while ( $tempTag->hasParent() )
{
    $tempTag = $tempTag->getParent();
    $Result['path'][] = array( 'tag_id' => $tempTag->attribute( 'id' ),
                               'text'   => $tempTag->attribute( 'keyword' ),
                               'url'    => 'tags/view/' . $tempTag->getUrl() );
}

$Result['path'] = array_reverse( $Result['path'] );
$Result['path'][] = array( 'tag_id' => $tags[0]->attribute( 'id' ),
                           'text'   => $tags[0]->attribute( 'keyword' ),
                           'url'    => false );

$contentInfoArray = array();
$contentInfoArray['persistent_variable'] = false;
if ( $tpl->variable( 'persistent_variable' ) !== false )
    $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );

$Result['content_info'] = $contentInfoArray;

?>
