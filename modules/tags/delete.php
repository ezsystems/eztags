<?php

$http = eZHTTPTool::instance();

$tagID = (int) $Params['TagID'];
$deleteAllowed = true;
$error = '';

$tag = eZTagsObject::fetchWithMainTranslation( $tagID );
if ( !$tag instanceof eZTagsObject )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

if ( $http->hasPostVariable( 'NoButton' ) )
    return $Module->redirectToView( 'id', array( $tag->attribute( 'id' ) ) );

if ( $tag->attribute( 'main_tag_id' ) != 0 )
    return $Module->redirectToView( 'delete', array( $tag->attribute( 'main_tag_id' ) ) );

if ( $tag->getSubTreeLimitationsCount() > 0 )
{
    $deleteAllowed = false;
    $error = ezpI18n::tr( 'extension/eztags/errors', 'Tag cannot be modified because it is being used as subtree limitation in one or more class attributes.' );
}

if ( $http->hasPostVariable( 'YesButton' ) && $deleteAllowed )
{
    $db = eZDB::instance();
    $db->begin();

    $parentTag = $tag->getParent();
    if ( $parentTag instanceof eZTagsObject )
        $parentTag->updateModified();

    eZTagsObject::recursiveTagDelete( $tag );

    /* Extended Hook */
    if ( class_exists( 'ezpEvent', false ) )
        $tag = ezpEvent::getInstance()->filter( 'tag/delete', $tag );

    $db->commit();

    if ( $parentTag instanceof eZTagsObject )
        return $Module->redirectToView( 'id', array( $parentTag->attribute( 'id' ) ) );
    else
        return $Module->redirectToView( 'dashboard', array() );
}

$tpl = eZTemplate::factory();

$tpl->setVariable( 'tag', $tag );
$tpl->setVariable( 'delete_allowed', $deleteAllowed );
$tpl->setVariable( 'error', $error );

$Result = array();
$Result['content']    = $tpl->fetch( 'design:tags/delete.tpl' );
$Result['ui_context'] = 'edit';
$Result['path']       = array( array( 'tag_id' => 0,
                                      'text'   => ezpI18n::tr( 'extension/eztags/tags/edit', 'Delete tag' ),
                                      'url'    => false ) );

$contentInfoArray = array();
$contentInfoArray['persistent_variable'] = false;
if ( $tpl->variable( 'persistent_variable' ) !== false )
    $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );

$Result['content_info'] = $contentInfoArray;

?>
