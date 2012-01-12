<?php

$http = eZHTTPTool::instance();

$tagID = (int) $Params['TagID'];
$warning = '';
$error = '';

if ( $tagID <= 0 )
{
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

$tag = eZTagsObject::fetch( $tagID );
if ( !( $tag instanceof eZTagsObject ) )
{
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

if ( $tag->attribute( 'main_tag_id' ) != 0 )
{
    return $Module->redirectToView( 'edit', array( $tag->attribute( 'main_tag_id' ) ) );
}

if ( $http->hasPostVariable( 'DiscardButton' ) )
{
    return $Module->redirectToView( 'id', array( $tagID ) );
}

if ( $tag->isInsideSubTreeLimit() )
{
    $warning = ezpI18n::tr( 'extension/eztags/warnings', 'TAKE CARE: Tag is inside class attribute subtree limit(s). If moved outside those limits, it could lead to inconsistency as objects could end up with tags that they are not supposed to have.' );
}

if ( $http->hasPostVariable( 'SaveButton' ) )
{
    if ( !( $http->hasPostVariable( 'TagEditKeyword' ) && strlen( trim( $http->postVariable( 'TagEditKeyword' ) ) ) > 0 ) )
    {
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Name cannot be empty.' );
    }

    if ( empty( $error ) && !( $http->hasPostVariable( 'TagEditParentID' ) && (int) $http->postVariable( 'TagEditParentID' ) >= 0 ) )
    {
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Selected target tag is invalid.' );
    }

    $newParentTag = eZTagsObject::fetch( (int) $http->postVariable( 'TagEditParentID' ) );
    $newParentID = ( $newParentTag instanceof eZTagsObject ) ? $newParentTag->attribute( 'id' ) : 0;

    $newKeyword = trim( $http->postVariable( 'TagEditKeyword' ) );
    if ( empty( $error ) && eZTagsObject::exists( $tag->attribute( 'id' ), $newKeyword, $newParentID ) )
    {
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Tag/synonym with that name already exists in selected location.' );
    }

    if ( empty( $error ) )
    {
        $updateDepth = false;
        $updatePathString = false;

        $db = eZDB::instance();
        $db->begin();

        $oldParentDepth = $tag->attribute( 'depth' ) - 1;
        $newParentDepth = ( $newParentTag instanceof eZTagsObject ) ? $newParentTag->attribute( 'depth' ) : 0;

        if ( $oldParentDepth != $newParentDepth )
            $updateDepth = true;

        if ( $tag->attribute( 'parent_id' ) != $newParentID )
        {
            $oldParentTag = $tag->getParent();
            if ( $oldParentTag instanceof eZTagsObject )
            {
                $oldParentTag->updateModified();
            }

            $synonyms = $tag->getSynonyms();
            foreach ( $synonyms as $synonym )
            {
                $synonym->setAttribute( 'parent_id', $newParentID );
                $synonym->store();
            }

            $updatePathString = true;
        }

        $tag->setAttribute( 'keyword', $newKeyword );
        $tag->setAttribute( 'parent_id', $newParentID );
        $tag->store();

        /* Extended Hook */
        if ( class_exists( 'ezpEvent', false ) ) {
            ezpEvent::getInstance()->filter( 'tag/edit', array(
                'tag'          => $tag,
                'oldParentTag' => $oldParentTag,
                'newParentTag' => $newParentTag,
                'move'         => $updatePathString ) );
        }

        if ( !$newParentTag instanceof eZTagsObject )
            $newParentTag = false;

        if ( $updatePathString )
            $tag->updatePathString( $newParentTag );

        if ( $updateDepth )
            $tag->updateDepth( $newParentTag );

        $tag->updateModified();
        $tag->registerSearchObjects();

        $db->commit();

        return $Module->redirectToView( 'id', array( $tagID ) );
    }
}

$tpl = eZTemplate::factory();

$tpl->setVariable( 'tag', $tag );
$tpl->setVariable( 'warning', $warning );
$tpl->setVariable( 'error', $error );

$Result = array();
$Result['content']    = $tpl->fetch( 'design:tags/edit.tpl' );
$Result['ui_context'] = 'edit';
$Result['path']       = array();

$tempTag = $tag;
while ( $tempTag->hasParent() )
{
    $tempTag = $tempTag->getParent();
    $Result['path'][] = array( 'tag_id' => $tempTag->attribute( 'id' ),
                               'text'   => $tempTag->attribute( 'keyword' ),
                               'url'    => false );
}

$Result['path'] = array_reverse( $Result['path'] );
$Result['path'][] = array( 'tag_id' => $tag->attribute( 'id' ),
                           'text'   => $tag->attribute( 'keyword' ),
                           'url'    => false );

$contentInfoArray = array();
$contentInfoArray['persistent_variable'] = false;
if ( $tpl->variable( 'persistent_variable' ) !== false )
    $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );

$Result['content_info'] = $contentInfoArray;

?>
