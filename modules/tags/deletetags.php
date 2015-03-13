<?php

/** @var eZModule $Module */
/** @var array $Params */

$http = eZHTTPTool::instance();

$tagIDs = $http->sessionVariable( 'eZTagsDeleteIDArray', $http->postVariable( 'SelectedIDArray' ), array() );
if ( !is_array( $tagIDs ) || empty( $tagIDs ) )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

/** @var eZTagsObject[] $tagsList */
$tagsList = eZTagsObject::fetchList( array( 'id' => array( $tagIDs ) ), null, null, true );
if ( !is_array( $tagsList ) || empty( $tagsList ) )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

$http->setSessionVariable( 'eZTagsDeleteIDArray', $tagIDs );

$parentTagID = (int) $tagsList[0]->attribute( 'parent_id' );

if ( $http->hasPostVariable( 'NoButton' ) )
{
    $http->removeSessionVariable( 'eZTagsDeleteIDArray' );

    if ( $parentTagID > 0 )
        return $Module->redirectToView( 'id', array( $parentTagID ) );

    return $Module->redirectToView( 'dashboard', array() );
}
else if ( $http->hasPostVariable( 'YesButton' ) )
{
    $db = eZDB::instance();

    foreach ( $tagsList as $tag )
    {
        if ( $tag->getSubTreeLimitationsCount() > 0 || $tag->attribute( 'main_tag_id' ) != 0 )
            continue;

        $db->begin();

        $parentTag = $tag->getParent( true );
        if ( $parentTag instanceof eZTagsObject )
            $parentTag->updateModified();

        /* Extended Hook */
        if ( class_exists( 'ezpEvent', false ) )
            ezpEvent::getInstance()->filter( 'tag/delete', $tag );

        $tag->recursivelyDeleteTag();

        $db->commit();
    }

    $http->removeSessionVariable( 'eZTagsDeleteIDArray' );

    if ( $parentTagID > 0 )
        return $Module->redirectToView( 'id', array( $parentTagID ) );

    return $Module->redirectToView( 'dashboard', array() );
}

$tpl = eZTemplate::factory();
$tpl->setVariable( 'tags', $tagsList );

$Result = array();
$Result['content']    = $tpl->fetch( 'design:tags/deletetags.tpl' );
$Result['ui_context'] = 'edit';
$Result['path']       = eZTagsObject::generateModuleResultPath( false, null,
                                                                ezpI18n::tr( 'extension/eztags/tags/edit', 'Delete tags' ) );
