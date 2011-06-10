<?php

$http = eZHTTPTool::instance();

$mainTagID = (int) $Params['MainTagID'];
$error = '';

if ( $mainTagID <= 0 )
{
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

$mainTag = eZTagsObject::fetch( $mainTagID );
if ( !( $mainTag instanceof eZTagsObject ) )
{
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

if ( $mainTag->MainTagID != 0 )
{
    return $Module->redirectToView( 'addsynonym', array( $mainTag->MainTagID ) );
}

if ( $http->hasPostVariable( 'DiscardButton' ) )
{
    return $Module->redirectToView( 'id', array( $mainTagID ) );
}

if ( $http->hasPostVariable( 'SaveButton' ) )
{
    if ( !( $http->hasPostVariable( 'TagEditKeyword' ) && strlen ( trim( $http->postVariable( 'TagEditKeyword' ) ) ) > 0 ) )
    {
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Name cannot be empty.' );
    }

    $newKeyword = trim( $http->postVariable( 'TagEditKeyword' ) );
    if ( empty( $error ) && eZTagsObject::exists( 0, $newKeyword, $mainTag->ParentID ) )
    {
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Tag/synonym with that name already exists in selected location.' );
    }

    if ( empty( $error ) )
    {
        $parentTag = eZTagsObject::fetch( $mainTag->ParentID );

        $db = eZDB::instance();
        $db->begin();

        $tag = new eZTagsObject( array( 'parent_id'   => $mainTag->ParentID,
                                        'main_tag_id' => $mainTagID,
                                        'keyword'     => $newKeyword,
                                        'depth'       => $mainTag->Depth,
                                        'path_string' => ( $parentTag instanceof eZTagsObject ) ? $parentTag->PathString : '/' ) );

        $tag->store();
        $tag->PathString = $tag->PathString . $tag->ID . '/';
        $tag->store();
        $tag->updateModified();

        $db->commit();

        return $Module->redirectToView( 'id', array( $tag->ID ) );
    }
}

$tpl = eZTemplate::factory();

$tpl->setVariable( 'main_tag', $mainTag );
$tpl->setVariable( 'error', $error );
$tpl->setVariable( 'ui_context', 'edit' );

$Result = array();
$Result['content']    = $tpl->fetch( 'design:tags/addsynonym.tpl' );
$Result['ui_context'] = 'edit';
$Result['path']       = array();

$tempTag = $mainTag;
while ( $tempTag->hasParent() )
{
    $Result['path'][] = array( 'tag_id' => $tempTag->ID,
                               'text'   => $tempTag->Keyword,
                               'url'    => false );
    $tempTag = $tempTag->getParent();
}

$Result['path'][] = array( 'tag_id' => $tempTag->ID,
                           'text'   => $tempTag->Keyword,
                           'url'    => false );

$Result['path'] = array_reverse( $Result['path'] );

$Result['path'][] = array( 'tag_id' => -1,
                           'text'   => ezpI18n::tr( 'extension/eztags/tags/edit', 'New synonym tag' ),
                           'url'    => false );

$contentInfoArray = array();
$contentInfoArray['persistent_variable'] = false;
if ( $tpl->variable( 'persistent_variable' ) !== false )
    $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );

$Result['content_info'] = $contentInfoArray;

?>
