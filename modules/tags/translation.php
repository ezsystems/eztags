<?php

$http = eZHTTPTool::instance();
$tagID = $http->hasPostVariable( 'TagID' ) ? (int) $http->postVariable( 'TagID' ) : 0;

if ( $tagID <= 0 )
{
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

$tag = eZTagsObject::fetch( $tagID );
if ( !$tag instanceof eZTagsObject )
{
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

if ( $http->hasPostVariable( 'RemoveTranslationButton' ) )
{
}
else if ( $http->hasPostVariable( 'UpdateMainLanguageButton' ) )
{
}
else if ( $http->hasPostVariable( 'UpdateAlwaysAvailableButton' ) )
{
}

return $Module->redirectToView( 'id', array( $tag->attribute( 'id' ) ) );

?>
