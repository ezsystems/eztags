<?php

$http = eZHTTPTool::instance();

$parentTagID = (int) $Params['ParentTagID'];

if ( $http->hasPostVariable( 'TagEditParentID' ) )
    $parentTagID = (int) $http->postVariable( 'TagEditParentID' );

$error = '';
$parentTag = false;

if ( $parentTagID < 0 )
{
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

if ( $parentTagID > 0 )
{
    $parentTag = eZTagsObject::fetch( $parentTagID );

    if ( !( $parentTag instanceof eZTagsObject ) )
    {
        return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
    }

    if ( $parentTag->attribute( 'main_tag_id' ) != 0 )
    {
        return $Module->redirectToView( 'add', array( $parentTag->attribute( 'main_tag_id' ) ) );
    }
}

$userLimitations = eZTagsTemplateFunctions::getSimplifiedUserAccess( 'tags', 'add' );
$hasAccess = false;

if ( !isset( $userLimitations['simplifiedLimitations']['Tag'] ) )
{
    $hasAccess = true;
}
else
{
    $parentTagPathString = ( $parentTag instanceof eZTagsObject ) ? $parentTag->attribute( 'path_string' ) : '/';
    foreach ( $userLimitations['simplifiedLimitations']['Tag'] as $key => $value )
    {
        if ( strpos( $parentTagPathString, '/' . $value . '/' ) !== false )
        {
            $hasAccess = true;
            break;
        }
    }
}

if ( !$hasAccess )
{
    return $Module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' );
}

if ( $http->hasPostVariable( 'DiscardButton' ) )
{
    if ( $parentTag instanceof eZTagsObject )
        return $Module->redirectToView( 'id', array( $parentTagID ) );
    else
        return $Module->redirectToView( 'dashboard', array() );
}

if ( $http->hasPostVariable('SaveButton' ) )
{
    if ( !( $http->hasPostVariable( 'TagEditKeyword' ) && strlen( trim( $http->postVariable( 'TagEditKeyword' ) ) ) > 0 ) )
    {
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Name cannot be empty.' );
    }

    $newKeyword = trim( $http->postVariable( 'TagEditKeyword' ) );
    if ( empty( $error ) && eZTagsObject::exists( 0, $newKeyword, ( $parentTag instanceof eZTagsObject ) ? $parentTag->attribute( 'id' ) : 0 ) )
    {
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Tag/synonym with that name already exists in selected location.' );
    }

    if ( empty( $error ) )
    {
        $db = eZDB::instance();
        $db->begin();

        $tag = new eZTagsObject( array( 'parent_id'   => ( $parentTag instanceof eZTagsObject ) ? $parentTag->attribute( 'id' ) : 0,
                                        'main_tag_id' => 0,
                                        'keyword'     => $newKeyword,
                                        'depth'       => ( $parentTag instanceof eZTagsObject ) ? (int) $parentTag->attribute( 'depth' ) + 1 : 1,
                                        'path_string' => ( $parentTag instanceof eZTagsObject ) ? $parentTag->attribute( 'path_string' ) : '/' ) );

        $tag->store();
        $tag->setAttribute( 'path_string', $tag->attribute( 'path_string' ) . $tag->attribute( 'id' ) . '/' );
        $tag->store();
        $tag->updateModified();

        /* Extended Hook */
        if ( class_exists( 'ezpEvent', false ) )
            ezpEvent::getInstance()->filter( 'tag/add', array( 'tag' => $tag, 'parentTag' => $parentTag ) );

        $db->commit();

        return $Module->redirectToView( 'id', array( $tag->attribute( 'id' ) ) );
    }
}

$tpl = eZTemplate::factory();

$tpl->setVariable( 'parent_id', $parentTagID );
$tpl->setVariable( 'error', $error );
$tpl->setVariable( 'ui_context', 'edit' );

$Result = array();
$Result['content']    = $tpl->fetch( 'design:tags/add.tpl' );
$Result['ui_context'] = 'edit';
$Result['path']       = array();

if ( $parentTag instanceof eZTagsObject )
{
    $tempTag = $parentTag;
    while ( $tempTag->hasParent() )
    {
        $Result['path'][] = array( 'tag_id' => $tempTag->attribute( 'id' ),
                                   'text'   => $tempTag->attribute( 'keyword' ),
                                   'url'    => false );
        $tempTag = $tempTag->getParent();
    }

    $Result['path'][] = array( 'tag_id' => $tempTag->attribute( 'id' ),
                               'text'   => $tempTag->attribute( 'keyword' ),
                               'url'    => false );

    $Result['path'] = array_reverse( $Result['path'] );
}

$Result['path'][] = array( 'tag_id' => -1,
                           'text'   => ezpI18n::tr( 'extension/eztags/tags/edit', 'New tag' ),
                           'url'    => false );

$contentInfoArray = array();
$contentInfoArray['persistent_variable'] = false;
if ( $tpl->variable( 'persistent_variable' ) !== false )
    $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );

$Result['content_info'] = $contentInfoArray;

?>
