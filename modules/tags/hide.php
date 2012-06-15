<?php

$http = eZHTTPTool::instance();

$tagID = (int) $Params['TagID'];
$action = $Params['Action'];

if ( $tagID <= 0 )
{
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

$tag = eZTagsObject::fetch( $tagID );
if ( !( $tag instanceof eZTagsObject ) )
{
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

if( !trim( $action ) )
{
    if( $tag->isHidden() )
    {
        $action = 'unhide';
    }
    else
    {
        $action = 'hide';
    }
}

if( !in_array( $action, array( 'hide', 'unhide' ) ) )
{
    return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

if ( $tag->attribute( 'main_tag_id' ) != 0 )
{
    return $Module->redirectToView( 'hide', array( $tag->attribute( 'main_tag_id' ) ) );
}

$doHide = $action === 'hide';

$db = eZDB::instance();
$db->begin();
$tag->setHidden( $doHide );
$tag->updateModified();
$tag->store();

//hide synonyms
$synonyms = $tag->getSynonyms();
foreach( $synonyms as $synonym )
{
    $synonym->setInvisible( $doHide );
    $synonym->updateModified();
    $synonym->store();
}

//hide descendant tags
$bitwiseOperator = $doHide ? '|' : '& ~';
$sql = 'UPDATE eztags
			SET hidden = hidden' . $bitwiseOperator . eZTagsObject::VISIBILITY_INVISIBLE .',
				modified = ' . time() . '
			WHERE path_string LIKE "' . $tag->attribute( 'path_string' ) . '%"';
$db->query( $sql );

$db->commit();

/* Extended Hook */
if ( class_exists( 'ezpEvent', false ) )
{
    $eventName = $doHide ? 'tag/hide' : 'tag/show';
    ezpEvent::getInstance()->filter( $eventName, $tag );
}


$redirectURI = $http->hasPostVariable( 'RedirectURI' ) ? $http->postVariable( 'RedirectURI' ) : $http->sessionVariable( 'LastAccessesURI', '/' );
$Module->redirectTo( $redirectURI );