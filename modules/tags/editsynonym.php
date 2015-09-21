<?php

/** @var eZModule $Module */
/** @var array $Params */

$http = eZHTTPTool::instance();

$tagID = (int) $Params['TagID'];
$locale = (string) $Params['Locale'];

if ( empty( $locale ) )
    $locale = $http->postVariable( 'Locale', false );

if ( $http->hasPostVariable( 'DiscardButton' ) )
{
    if ( $locale !== false )
    {
        $tag = eZTagsObject::fetchWithMainTranslation( $tagID );
        if ( $tag instanceof eZTagsObject )
        {
            $tagTranslation = eZTagsKeyword::fetch( $tag->attribute( 'id' ), $locale, true );
            if ( $tagTranslation instanceof eZTagsKeyword && $tagTranslation->attribute( 'status' ) == eZTagsKeyword::STATUS_DRAFT )
            {
                $tagTranslation->remove();
                $tag->updateLanguageMask();
            }
        }
    }

    return $Module->redirectToView( 'id', array( $tagID ) );
}

if ( $locale === false )
{
    $tag = eZTagsObject::fetchWithMainTranslation( $tagID );
    if ( !$tag instanceof eZTagsObject )
        return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

    $languages = eZContentLanguage::fetchList();
    if ( !is_array( $languages ) || empty( $languages ) )
        return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

    if ( count( $languages ) == 1 )
        return $Module->redirectToView( 'editsynonym', array( $tag->attribute( 'id' ), current( $languages )->attribute( 'locale' ) ) );

    $tpl = eZTemplate::factory();

    $tpl->setVariable( 'tag', $tag );
    $tpl->setVariable( 'languages', $languages );

    $Result = array();
    $Result['content']    = $tpl->fetch( 'design:tags/editsynonym_languages.tpl' );
    $Result['ui_context'] = 'edit';
    $Result['path']       = eZTagsObject::generateModuleResultPath( $tag );

    return;
}

/** @var eZContentLanguage $language */
$language = eZContentLanguage::fetchByLocale( $locale );
if ( !$language instanceof eZContentLanguage )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

$tag = eZTagsObject::fetchWithMainTranslation( $tagID );
if ( !$tag instanceof eZTagsObject )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

if ( $tag->attribute( 'main_tag_id' ) == 0 )
    return $Module->redirectToView( 'edit', array( $tag->attribute( 'id' ) ) );

$tagTranslation = eZTagsKeyword::fetch( $tag->attribute( 'id' ), $language->attribute( 'locale' ), true );
if ( !$tagTranslation instanceof eZTagsKeyword )
{
    $tagTranslation = new eZTagsKeyword( array( 'keyword_id'  => $tag->attribute( 'id' ),
                                                'keyword'     => '',
                                                'language_id' => $language->attribute( 'id' ),
                                                'locale'      => $language->attribute( 'locale' ),
                                                'status'      => eZTagsKeyword::STATUS_DRAFT ) );

    $tagTranslation->store();
    $tag->updateLanguageMask();
}

$tag = eZTagsObject::fetch( $tagID, $language->attribute( 'locale' ) );
if ( !$tag instanceof eZTagsObject )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

$error = '';

if ( $http->hasPostVariable( 'SaveButton' ) )
{
    $newKeyword = trim( $http->postVariable( 'TagEditKeyword', '' ) );
    if ( empty( $newKeyword ) )
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Name cannot be empty.' );

    if ( empty( $error ) && eZTagsObject::exists( $tag->attribute( 'id' ), $newKeyword, $tag->attribute( 'parent_id' ) ) )
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Tag/synonym with that translation already exists in selected location.' );

    if ( empty( $error ) )
    {
        $db = eZDB::instance();
        $db->begin();

        $tagTranslation->setAttribute( 'keyword', $newKeyword );
        $tagTranslation->setAttribute( 'status', eZTagsKeyword::STATUS_PUBLISHED );
        $tagTranslation->store();

        if ( $http->hasPostVariable( 'SetAsMainTranslation' ) )
            $tag->updateMainTranslation( $language->attribute( 'locale' ) );

        $tag->setAlwaysAvailable( $http->hasPostVariable( 'AlwaysAvailable' ) );

        $tag->store();
        $tag->registerSearchObjects();

        $db->commit();

        /* Extended Hook */
        if ( class_exists( 'ezpEvent', false ) )
        {
            $tagParent = $tag->getParent( true );
            ezpEvent::getInstance()->filter(
                'tag/edit',
                array(
                    'tag'          => $tag,
                    'oldParentTag' => $tagParent,
                    'newParentTag' => $tagParent,
                    'move'         => false
                )
            );
        }

        return $Module->redirectToView( 'id', array( $tag->attribute( 'id' ) ) );
    }
}

$tpl = eZTemplate::factory();

$tpl->setVariable( 'tag', $tag );
$tpl->setVariable( 'language', $language );
$tpl->setVariable( 'error', $error );

$Result = array();
$Result['content']    = $tpl->fetch( 'design:tags/editsynonym.tpl' );
$Result['ui_context'] = 'edit';
$Result['path']       = eZTagsObject::generateModuleResultPath( $tag );
