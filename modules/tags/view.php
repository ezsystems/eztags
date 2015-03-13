<?php

/** @var eZModule $Module */
/** @var array $Params */

$http = eZHTTPTool::instance();
$keywordArray = $Params['Parameters'];
if ( !is_array( $keywordArray ) || empty( $keywordArray ) )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

<<<<<<< HEAD
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
=======
$tag = eZTagsObject::fetchByUrl( $keywordArray );
if ( !$tag instanceof eZTagsObject )
>>>>>>> 06abc6e4d24cb0184dd64c8a211ac25dcafa5b1b
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
<<<<<<< HEAD
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
=======
$Result['path']    = eZTagsObject::generateModuleResultPath( $tag, true, false, false );
>>>>>>> 06abc6e4d24cb0184dd64c8a211ac25dcafa5b1b
