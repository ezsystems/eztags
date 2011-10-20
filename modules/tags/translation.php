<?php

$http = eZHTTPTool::instance();

$tagID = $http->hasPostVariable( 'TagID' ) ? (int) $http->postVariable( 'TagID' ) : 0;

$tag = eZTagsObject::fetchWithMainTranslation( $tagID );
if ( !$tag instanceof eZTagsObject )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

$db = eZDB::instance();

if ( $http->hasPostVariable( 'RemoveTranslationButton' ) )
{
    if ( $http->hasPostVariable( 'Locale' ) && is_array( $http->postVariable( 'Locale' ) ) )
    {
        $mainTranslation = $tag->getMainTranslation();

        $db->begin();
        foreach ( $http->postVariable( 'Locale' ) as $locale )
        {
            $translation = $tag->translationByLocale( $locale );
            if ( $translation instanceof eZTagsKeyword && $translation->attribute( 'locale' ) != $mainTranslation->attribute( 'locale' ) )
                $translation->remove();
        }
        $db->commit();
    }
}
else if ( $http->hasPostVariable( 'UpdateMainTranslationButton' ) )
{
    if ( $http->hasPostVariable( 'MainLocale' ) )
    {
        $db->begin();
        $tag->updateMainTranslation( $http->postVariable( 'MainLocale' ) );
        $tag->updateModified();
        $db->commit();
    }
}
else if ( $http->hasPostVariable( 'UpdateAlwaysAvailableButton' ) )
{
    $db->begin();
    $alwaysAvailable = $http->hasPostVariable( 'AlwaysAvailable' );
    $tag->setAlwaysAvailable( $alwaysAvailable );
    $db->commit();
}

return $Module->redirectToView( 'id', array( $tag->attribute( 'id' ) ) );

?>
