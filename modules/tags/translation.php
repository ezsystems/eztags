<?php

$db = eZDB::instance();
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
	if ( $http->hasPostVariable( 'LanguageID' ) && is_array( $http->postVariable( 'LanguageID' ) ) )
	{
		$db->begin();
		foreach ( $http->postVariable( 'LanguageID' ) as $languageID )
		{
			$translation = $tag->translationByLanguageID( (int) $languageID );
			if ( $translation instanceof eZTagsKeyword && $translation->attribute( 'language_id' ) != $tag->attribute( 'main_language_id' ) )
			{
				$translation->remove();
			}
		}
		$db->commit();
	}
}
else if ( $http->hasPostVariable( 'UpdateMainLanguageButton' ) )
{
	if ( $http->hasPostVariable( 'MainLanguageID' ) )
	{
		$db->begin();
		$tag->updateMainTranslation( $http->postVariable( 'MainLanguageID' ), true );
		$tag->updateModified();
		$db->commit();
	}
}
else if ( $http->hasPostVariable( 'UpdateAlwaysAvailableButton' ) )
{
	$db->begin();
	$alwaysAvailable = $http->hasPostVariable( 'AlwaysAvailable' );
	$tag->setAlwaysAvailable( $alwaysAvailable, true );
	$db->commit();
}

return $Module->redirectToView( 'id', array( $tag->attribute( 'id' ) ) );

?>
