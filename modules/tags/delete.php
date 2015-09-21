<?php

/** @var eZModule $Module */
/** @var array $Params */

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

    $parentTag = $tag->getParent( true );
    if ( $parentTag instanceof eZTagsObject )
        $parentTag->updateModified();

    /* Extended Hook */
    if ( class_exists( 'ezpEvent', false ) )
        ezpEvent::getInstance()->filter( 'tag/delete', $tag );

    $tag->recursivelyDeleteTag();

    $db->commit();

    if ( $parentTag instanceof eZTagsObject )
        return $Module->redirectToView( 'id', array( $parentTag->attribute( 'id' ) ) );

    return $Module->redirectToView( 'dashboard', array() );
}

$tpl = eZTemplate::factory();

$tpl->setVariable( 'tag', $tag );
$tpl->setVariable( 'delete_allowed', $deleteAllowed );
$tpl->setVariable( 'error', $error );

$Result = array();
$Result['content']    = $tpl->fetch( 'design:tags/delete.tpl' );
$Result['ui_context'] = 'edit';
$Result['path']       = eZTagsObject::generateModuleResultPath( false, null,
                                                                ezpI18n::tr( 'extension/eztags/tags/edit', 'Delete tag' ) );
