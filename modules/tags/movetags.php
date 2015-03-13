<?php

/** @var eZModule $Module */
/** @var array $Params */

$http = eZHTTPTool::instance();

$tagIDs = $http->sessionVariable( 'eZTagsMoveIDArray', $http->postVariable( 'SelectedIDArray', array() ) );
if ( !is_array( $tagIDs ) || empty( $tagIDs ) )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

/** @var eZTagsObject[] $tagsList */
$tagsList = eZTagsObject::fetchList( array( 'id' => array( $tagIDs ) ), null, null, true );
if ( !is_array( $tagsList ) || empty( $tagsList ) )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

$http->setSessionVariable( 'eZTagsMoveIDArray', $tagIDs );

$parentTagID = (int) $tagsList[0]->attribute( 'parent_id' );

if ( $http->hasPostVariable( 'DiscardButton' ) )
{
    $http->removeSessionVariable( 'eZTagsMoveIDArray' );

    if ( $parentTagID > 0 )
        return $Module->redirectToView( 'id', array( $parentTagID ) );

    return $Module->redirectToView( 'dashboard', array() );
}

$error = '';
$newParentID = (int) $http->postVariable( 'TagEditParentID', 0 );
$newParentTag = eZTagsObject::fetchWithMainTranslation( $newParentID );
if ( !$newParentTag instanceof eZTagsObject && $newParentID > 0 )
    $error = ezpI18n::tr( 'extension/eztags/errors', 'Selected target tag is invalid.' );

if ( empty( $error ) && $http->hasPostVariable( 'SaveButton' ) )
{
    $db = eZDB::instance();

    $unmovableTags = array();

    foreach ( $tagsList as $tag )
    {
        if ( $tag->attribute( 'main_tag_id' ) != 0 )
            continue;

        if ( (int) $tag->attribute( 'parent_id' ) === $newParentID )
            continue;

        // @todo: check for subtree limits

        $tagKeywords = array_map( function( $translation )
            {
                /** @var eZTagsKeyword $translation */
                return $translation->attribute( 'keyword' );
            },
            $tag->getTranslations()
        );

        foreach ( $tagKeywords as $tagKeyword )
        {
            if ( eZTagsObject::exists( $tag->attribute( 'id' ), $tagKeyword, $newParentID ) )
            {
                $unmovableTags[] = $tag;
                continue 2;
            }
        }

        $db->begin();

        $updateDepth = false;

        $oldParentDepth = $tag->attribute( 'depth' ) - 1;
        $newParentDepth = $newParentTag instanceof eZTagsObject ? $newParentTag->attribute( 'depth' ) : 0;

        if ( $oldParentDepth != $newParentDepth )
            $updateDepth = true;

        $oldParentTag = $tag->getParent( true );
        if ( $oldParentTag instanceof eZTagsObject )
            $oldParentTag->updateModified();

        $synonyms = $tag->getSynonyms( true );
        foreach ( $synonyms as $synonym )
        {
            $synonym->setAttribute( 'parent_id', $newParentID );
            $synonym->store();
        }

        $tag->setAttribute( 'parent_id', $newParentID );
        $tag->store();

        /* Extended Hook */
        if ( class_exists( 'ezpEvent', false ) )
        {
            ezpEvent::getInstance()->filter(
                'tag/edit',
                array(
                    'tag'          => $tag,
                    'oldParentTag' => $oldParentTag,
                    'newParentTag' => $newParentTag,
                    'move'         => true
                )
            );
        }

        $tag->updatePathString();

        if ( $updateDepth )
            $tag->updateDepth();

        $tag->updateModified();
        $tag->registerSearchObjects();

        $db->commit();
    }

    $http->removeSessionVariable( 'eZTagsMoveIDArray' );

    if ( empty( $unmovableTags ) )
    {
        if ( $parentTagID > 0 )
            return $Module->redirectToView( 'id', array( $parentTagID ) );

        return $Module->redirectToView( 'dashboard', array() );
    }

    $tpl = eZTemplate::factory();
    $tpl->setVariable( 'unmovable_tags', $unmovableTags );
    $tpl->setVariable( 'parent_tag_id', $parentTagID );

    $Result = array();
    $Result['content']    = $tpl->fetch( 'design:tags/movetags_result.tpl' );
    $Result['ui_context'] = 'edit';
    $Result['path']       = eZTagsObject::generateModuleResultPath( false, null,
                                                                    ezpI18n::tr( 'extension/eztags/tags/edit', 'Move tags' ) );
    return;
}

$tpl = eZTemplate::factory();
$tpl->setVariable( 'error', $error );
$tpl->setVariable( 'tags', $tagsList );

$Result = array();
$Result['content']    = $tpl->fetch( 'design:tags/movetags.tpl' );
$Result['ui_context'] = 'edit';
$Result['path']       = eZTagsObject::generateModuleResultPath( false, null,
                                                                ezpI18n::tr( 'extension/eztags/tags/edit', 'Move tags' ) );
