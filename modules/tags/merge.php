<?php

/** @var eZModule $Module */
/** @var array $Params */

$http = eZHTTPTool::instance();

$tagID = (int) $Params['TagID'];
$mergeAllowed = true;
$warning = '';
$error = '';

$tag = eZTagsObject::fetchWithMainTranslation( $tagID );
if ( !$tag instanceof eZTagsObject )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

if ( $http->hasPostVariable( 'DiscardButton' ) )
    return $Module->redirectToView( 'id', array( $tag->attribute( 'id' ) ) );

if ( $tag->attribute( 'main_tag_id' ) != 0 )
    return $Module->redirectToView( 'merge', array( $tag->attribute( 'main_tag_id' ) ) );

if ( $tag->getSubTreeLimitationsCount() > 0 )
{
    $mergeAllowed = false;
    $error = ezpI18n::tr( 'extension/eztags/errors', 'Tag cannot be modified because it is being used as subtree limitation in one or more class attributes.' );
}

if ( $tag->isInsideSubTreeLimit() )
    $warning = ezpI18n::tr( 'extension/eztags/warnings', 'TAKE CARE: Tag is inside class attribute subtree limit(s). If moved outside those limits, it could lead to inconsistency as objects could end up with tags that they are not supposed to have.' );

if ( $http->hasPostVariable( 'SaveButton' ) && $mergeAllowed )
{
    $mainTagID = (int) $http->postVariable( 'MainTagID', 0 );
    $mainTag = eZTagsObject::fetchWithMainTranslation( $mainTagID );
    if ( !$mainTag instanceof eZTagsObject )
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Selected target tag is invalid.' );

    if ( empty( $error ) )
    {
        $db = eZDB::instance();
        $db->begin();

        $oldParentTag = false;
        if ( $tag->attribute( 'parent_id' ) != $mainTag->attribute( 'parent_id' ) )
        {
            $oldParentTag = $tag->getParent( true );
            if ( $oldParentTag instanceof eZTagsObject )
                $oldParentTag->updateModified();
        }

        /* Extended Hook */
        if ( class_exists( 'ezpEvent', false ) )
        {
            ezpEvent::getInstance()->filter( 'tag/merge', array(
                'tag'          => $tag,
                'newParentTag' => $mainTag,
                'oldParentTag' => $oldParentTag ) );
        }

        $tag->moveChildrenBelowAnotherTag( $mainTag );

        foreach ( $tag->getSynonyms( true ) as $synonym )
        {
            $synonym->registerSearchObjects();
            $synonym->transferObjectsToAnotherTag( $mainTag );
            $synonym->remove();
        }

        $tag->registerSearchObjects();
        $tag->transferObjectsToAnotherTag( $mainTag );
        $tag->remove();

        $mainTag->updateModified();

        $db->commit();

        return $Module->redirectToView( 'id', array( $mainTag->attribute( 'id' ) ) );
    }
}

$tpl = eZTemplate::factory();

$tpl->setVariable( 'tag', $tag );
$tpl->setVariable( 'merge_allowed', $mergeAllowed );
$tpl->setVariable( 'warning', $warning );
$tpl->setVariable( 'error', $error );

$Result = array();
$Result['content']    = $tpl->fetch( 'design:tags/merge.tpl' );
$Result['ui_context'] = 'edit';
$Result['path']       = eZTagsObject::generateModuleResultPath( $tag );
