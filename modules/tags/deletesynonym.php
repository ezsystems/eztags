<?php

/** @var eZModule $Module */
/** @var array $Params */

$http = eZHTTPTool::instance();

$tagID = (int) $Params['TagID'];

$tag = eZTagsObject::fetchWithMainTranslation( $tagID );
if ( !$tag instanceof eZTagsObject )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

if ( $http->hasPostVariable( 'NoButton' ) )
    return $Module->redirectToView( 'id', array( $tag->attribute( 'id' ) ) );

if ( $tag->attribute( 'main_tag_id' ) == 0 )
    return $Module->redirectToView( 'delete', array( $tag->attribute( 'id' ) ) );

if ( $http->hasPostVariable( 'YesButton' ) )
{
    $db = eZDB::instance();
    $db->begin();

    $parentTag = $tag->getParent( true );
    if ( $parentTag instanceof eZTagsObject )
        $parentTag->updateModified();

    $tag->registerSearchObjects();

    if ( $http->hasPostVariable( 'TransferObjectsToMainTag' ) )
    {
        /* Extended Hook */
        if ( class_exists( 'ezpEvent', false ) )
        {
            ezpEvent::getInstance()->filter(
                'tag/transferobjects',
                array(
                    'tag' => $tag,
                    'newTag' => $tag->getMainTag()
                )
            );
        }

        $tag->transferObjectsToAnotherTag( $tag->attribute( 'main_tag_id' ) );
    }

    /* Extended Hook */
    if ( class_exists( 'ezpEvent', false ) )
    {
        ezpEvent::getInstance()->filter( 'tag/delete', $tag );
    }

    $tag->remove();

    $db->commit();

    return $Module->redirectToView( 'id', array( $tag->attribute( 'main_tag_id' ) ) );
}

$tpl = eZTemplate::factory();

$tpl->setVariable( 'tag', $tag );

$Result = array();
$Result['content']    = $tpl->fetch( 'design:tags/deletesynonym.tpl' );
$Result['ui_context'] = 'edit';
$Result['path']       = eZTagsObject::generateModuleResultPath( false, null,
                                                                ezpI18n::tr( 'extension/eztags/tags/edit', 'Delete synonym' ) );
