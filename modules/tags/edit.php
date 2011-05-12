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

if ( $tag->MainTagID != 0 )
{
    return $Module->redirectToView( 'edit', array( $tag->MainTagID ) );
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
    $newParentID = ( $newParentTag instanceof eZTagsObject ) ? $newParentTag->ID : 0;

    $newKeyword = trim( $http->postVariable( 'TagEditKeyword' ) );
    if ( empty( $error ) && eZTagsObject::exists( $tag->ID, $newKeyword, $newParentID ) )
    {
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Tag/synonym with that name already exists in selected location.' );
    }

    if ( empty( $error ) )
    {
        $db = eZDB::instance();
        $db->begin();

        if ( $tag->ParentID != $newParentID )
        {
            $oldParentTag = $tag->getParent();
            if ( $oldParentTag instanceof eZTagsObject )
            {
                $oldParentTag->updateModified();
            }

            $synonyms = $tag->getSynonyms();
            foreach ( $synonyms as $synonym )
            {
                $synonym->ParentID = $newParentID;
                $synonym->store();
            }
        }

        $tag->Keyword = $newKeyword;
        $tag->ParentID = $newParentID;
        $tag->store();
        $tag->updatePathString( ( $newParentTag instanceof eZTagsObject ) ? $newParentTag : false );
        $tag->updateModified();
        $tag->registerSearchObjects();

        /* Extended Hook */
        if ( class_exists( 'ezpEvent', false ) )
            $tag = ezpEvent::getInstance()->filter( 'tag/edit', $tag );

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
    $Result['path'][] = array( 'tag_id' => $tempTag->ID,
                               'text'   => $tempTag->Keyword,
                               'url'    => false );
}

$Result['path'] = array_reverse( $Result['path'] );
$Result['path'][] = array( 'tag_id' => $tag->ID,
                           'text'   => $tag->Keyword,
                           'url'    => false );

$contentInfoArray = array();
$contentInfoArray['persistent_variable'] = false;
if ( $tpl->variable( 'persistent_variable' ) !== false )
    $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );

$Result['content_info'] = $contentInfoArray;

?>
