<?php

/** @var eZModule $Module */
/** @var array $Params */

$http = eZHTTPTool::instance();

$tagID = (int) $Params['TagID'];
$convertAllowed = true;
$warning = '';
$error = '';

$tag = eZTagsObject::fetchWithMainTranslation( $tagID );
if ( !$tag instanceof eZTagsObject )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

if ( $http->hasPostVariable( 'DiscardButton' ) )
    return $Module->redirectToView( 'id', array( $tag->attribute( 'id' ) ) );

if ( $tag->attribute( 'main_tag_id' ) != 0 )
    return $Module->redirectToView( 'makesynonym', array( $tag->attribute( 'main_tag_id' ) ) );

if ( $tag->getSubTreeLimitationsCount() > 0 )
{
    $convertAllowed = false;
    $error = ezpI18n::tr( 'extension/eztags/errors', 'Tag cannot be modified because it is being used as subtree limitation in one or more class attributes.' );
}

if ( $tag->isInsideSubTreeLimit() )
    $warning = ezpI18n::tr( 'extension/eztags/warnings', 'TAKE CARE: Tag is inside class attribute subtree limit(s). If moved outside those limits, it could lead to inconsistency as objects could end up with tags that they are not supposed to have.' );

if ( $http->hasPostVariable( 'SaveButton' ) && $convertAllowed )
{
    $mainTagID = (int) $http->postVariable( 'MainTagID', 0 );
    $mainTag = eZTagsObject::fetchWithMainTranslation( $mainTagID );
    if ( !$mainTag instanceof eZTagsObject )
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Selected target tag is invalid.' );

    if ( empty( $error ) && eZTagsObject::exists( $tag->attribute( 'id' ), $tag->attribute( 'keyword' ), $mainTag->attribute( 'parent_id' ) ) )
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Tag/synonym with that translation already exists in selected location.' );

    if ( empty( $error ) )
    {
        $updateDepth = false;
        $updatePathString = false;

        $db = eZDB::instance();
        $db->begin();

        if ( $tag->attribute( 'depth' ) != $mainTag->attribute( 'depth' ) )
            $updateDepth = true;

        if ( $tag->attribute( 'parent_id' ) != $mainTag->attribute( 'parent_id' ) )
        {
            $oldParentTag = $tag->getParent( true );
            if ( $oldParentTag instanceof eZTagsObject )
                $oldParentTag->updateModified();

            $updatePathString = true;
        }

        $tag->moveChildrenBelowAnotherTag( $mainTag );

        $synonyms = $tag->getSynonyms( true );
        foreach ( $synonyms as $synonym )
        {
            $synonym->setAttribute( 'parent_id', $mainTag->attribute( 'parent_id' ) );
            $synonym->setAttribute( 'main_tag_id', $mainTag->attribute( 'id' ) );
            $synonym->store();
        }

        $tag->setAttribute( 'parent_id', $mainTag->attribute( 'parent_id' ) );
        $tag->setAttribute( 'main_tag_id', $mainTag->attribute( 'id' ) );
        $tag->store();

        if ( $updatePathString )
            $tag->updatePathString();

        if ( $updateDepth )
            $tag->updateDepth();

        $tag->updateModified();

        if ( eZINI::instance( 'eztags.ini' )->variable( 'SearchSettings', 'IndexSynonyms' ) !== 'enabled' )
            $tag->registerSearchObjects();

        $db->commit();

        /* Extended Hook */
        if ( class_exists( 'ezpEvent', false ) )
        {
            ezpEvent::getInstance()->filter(
                'tag/makesynonym',
                array(
                    'tag' => $tag,
                    'mainTag' => $mainTag
                )
            );
        }

        return $Module->redirectToView( 'id', array( $tag->attribute( 'id' ) ) );
    }
}

$tpl = eZTemplate::factory();

$tpl->setVariable( 'tag', $tag );
$tpl->setVariable( 'convert_allowed', $convertAllowed );
$tpl->setVariable( 'warning', $warning );
$tpl->setVariable( 'error', $error );

$Result = array();
$Result['content']    = $tpl->fetch( 'design:tags/makesynonym.tpl' );
$Result['ui_context'] = 'edit';
$Result['path']       = eZTagsObject::generateModuleResultPath( $tag );
