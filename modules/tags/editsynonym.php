<?php

$http = eZHTTPTool::instance();

$tagID = (int) $Params['TagID'];
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

if ( $tag->attribute( 'main_tag_id' ) == 0 )
{
    return $Module->redirectToView( 'edit', array( $tagID ) );
}

if ( $http->hasPostVariable( 'DiscardButton' ) )
{
    return $Module->redirectToView( 'id', array( $tagID ) );
}

if ( $http->hasPostVariable( 'SaveButton' ) )
{
    if ( !( $http->hasPostVariable( 'TagEditKeyword' ) && strlen( trim( $http->postVariable( 'TagEditKeyword' ) ) ) > 0 ) )
    {
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Name cannot be empty.' );
    }

    $newKeyword = trim( $http->postVariable( 'TagEditKeyword' ) );
    if ( empty( $error ) && eZTagsObject::exists( $tag->attribute( 'id' ), $newKeyword, $tag->attribute( 'parent_id' ) ) )
    {
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Tag/synonym with that name already exists in selected location.' );
    }

    if ( empty( $error ) )
    {
        $db = eZDB::instance();
        $db->begin();

        $tag->setAttribute( 'keyword', $newKeyword );
        $tag->store();

        /* Extended Hook */
        if ( class_exists( 'ezpEvent', false ) )
        {
            $tagParent = $tag->getParent();
            ezpEvent::getInstance()->filter( 'tag/edit', array(
                'tag'          => $tag,
                'oldParentTag' => $tagParent,
                'newParentTag' => $tagParent,
                'move'         => false ) );
        }

        $tag->registerSearchObjects();

        $db->commit();

        return $Module->redirectToView( 'id', array( $tagID ) );
    }
}

$tpl = eZTemplate::factory();

$tpl->setVariable( 'tag', $tag );
$tpl->setVariable( 'error', $error );

$Result = array();
$Result['content']    = $tpl->fetch( 'design:tags/editsynonym.tpl' );
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
