<?php

/** @var eZModule $Module */
/** @var array $Params */

$http = eZHTTPTool::instance();

$parentTagID = (int) $http->postVariable( 'TagEditParentID', $Params['ParentTagID'] );
if ( $parentTagID < 0 )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

$locale = (string) $Params['Locale'];
if ( empty( $locale ) )
    $locale = $http->postVariable( 'Locale', false );

$parentTag = false;
if ( $parentTagID > 0 )
{
    $parentTag = eZTagsObject::fetchWithMainTranslation( $parentTagID );
    if ( !$parentTag instanceof eZTagsObject )
        return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

    if ( $parentTag->attribute( 'main_tag_id' ) != 0 )
        return $Module->redirectToView( 'add', array( $parentTag->attribute( 'main_tag_id' ) ) );
}

if ( $http->hasPostVariable( 'DiscardButton' ) )
{
    if ( $parentTag instanceof eZTagsObject )
        return $Module->redirectToView( 'id', array( $parentTag->attribute( 'id' ) ) );

    return $Module->redirectToView( 'dashboard', array() );
}

$userLimitations = eZTagsTemplateFunctions::getSimplifiedUserAccess( 'tags', 'add' );
$hasAccess = false;

if ( !isset( $userLimitations['simplifiedLimitations']['Tag'] ) )
{
    $hasAccess = true;
}
else
{
    $parentTagPathString = $parentTag instanceof eZTagsObject ? $parentTag->attribute( 'path_string' ) : '/';
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
    return $Module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' );

if ( $locale === false )
{
    /** @var eZContentLanguage[] $languages */
    $languages = eZContentLanguage::prioritizedLanguages();
    if ( !is_array( $languages ) || empty( $languages ) )
        return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

    if ( count( $languages ) == 1 )
        return $Module->redirectToView( 'add', array( $parentTagID, $languages[0]->attribute( 'locale' ) ) );

    $tpl = eZTemplate::factory();

    $tpl->setVariable( 'languages', $languages );
    $tpl->setVariable( 'parent_id', $parentTagID );
    $tpl->setVariable( 'ui_context', 'edit' );

    $Result = array();
    $Result['content']    = $tpl->fetch( 'design:tags/add_languages.tpl' );
    $Result['ui_context'] = 'edit';
    $Result['path']       = eZTagsObject::generateModuleResultPath( $parentTag, null,
                                                                    ezpI18n::tr( 'extension/eztags/tags/edit', 'New tag' ) );

    return;
}

/** @var eZContentLanguage $language */
$language = eZContentLanguage::fetchByLocale( $locale );
if ( !$language instanceof eZContentLanguage )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

$error = '';

if ( $http->hasPostVariable('SaveButton' ) )
{
    $newKeyword = trim( $http->postVariable( 'TagEditKeyword', '' ) );
    if ( empty( $newKeyword ) )
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Name cannot be empty.' );

    if ( empty( $error ) && eZTagsObject::exists( 0, $newKeyword, $parentTag instanceof eZTagsObject ? $parentTag->attribute( 'id' ) : 0 ) )
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Tag/synonym with that translation already exists in selected location.' );

    if ( empty( $error ) )
    {
        $db = eZDB::instance();
        $db->begin();

        $languageMask = eZContentLanguage::maskByLocale( array( $language->attribute( 'locale' ) ), $http->hasPostVariable( 'AlwaysAvailable' ) );

        $tag = new eZTagsObject( array( 'parent_id'        => $parentTagID,
                                        'main_tag_id'      => 0,
                                        'depth'            => $parentTag instanceof eZTagsObject ? $parentTag->attribute( 'depth' ) + 1 : 1,
                                        'path_string'      => $parentTag instanceof eZTagsObject ? $parentTag->attribute( 'path_string' ) : '/',
                                        'main_language_id' => $language->attribute( 'id' ),
                                        'language_mask'    => $languageMask ), $language->attribute( 'locale' ) );
        $tag->store();

        $translation = new eZTagsKeyword( array( 'keyword_id'  => $tag->attribute( 'id' ),
                                                 'language_id' => $language->attribute( 'id' ),
                                                 'keyword'     => $newKeyword,
                                                 'locale'      => $language->attribute( 'locale' ),
                                                 'status'      => eZTagsKeyword::STATUS_PUBLISHED ) );

        if ( $http->hasPostVariable( 'AlwaysAvailable' ) )
            $translation->setAttribute( 'language_id', $translation->attribute( 'language_id' ) + 1 );

        $translation->store();

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
$tpl->setVariable( 'language', $language );
$tpl->setVariable( 'error', $error );
$tpl->setVariable( 'ui_context', 'edit' );

$Result = array();
$Result['content']    = $tpl->fetch( 'design:tags/add.tpl' );
$Result['ui_context'] = 'edit';
$Result['path']       = eZTagsObject::generateModuleResultPath( $parentTag, null,
                                                                ezpI18n::tr( 'extension/eztags/tags/edit', 'New tag' ) );
