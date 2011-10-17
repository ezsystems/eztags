<?php

$http = eZHTTPTool::instance();

$tagID = (int) $Params['TagID'];
$locale = (string) $Params['Locale'];

if ( $http->hasPostVariable( 'DiscardButton' ) )
    return $Module->redirectToView( 'id', array( $tagID ) );

if ( empty( $locale ) )
    $locale = $http->hasPostVariable( 'Locale' ) ? $http->postVariable( 'Locale' ) : false;

if ( $locale === false )
{
    $tag = eZTagsObject::fetchWithMainTranslation( $tagID );
    if ( !$tag instanceof eZTagsObject )
        return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

    $languages = eZContentLanguage::fetchList();
    if ( !is_array( $languages ) || empty( $languages ) )
        return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

    if ( count( $languages ) == 1 )
        return $Module->redirectToView( 'edit', array( $tag->attribute( 'id' ), $languages[0]->attribute( 'locale' ) ) );

    $tpl = eZTemplate::factory();

    $tpl->setVariable( 'tag', $tag );
    $tpl->setVariable( 'languages', $languages );

    $Result = array();
    $Result['content']    = $tpl->fetch( 'design:tags/edit_languages.tpl' );
    $Result['ui_context'] = 'edit';
    $Result['path']       = eZTagsObject::generateModuleResultPath( $tag );

    return;
}

$language = eZContentLanguage::fetchByLocale( $locale );
if ( !$language instanceof eZContentLanguage )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

$tag = eZTagsObject::fetchByLocale( $tagID, $language->attribute( 'locale' ), true );
if ( !$tag instanceof eZTagsObject )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

if ( $tag->attribute( 'main_tag_id' ) != 0 )
    return $Module->redirectToView( 'edit', array( $tag->attribute( 'main_tag_id' ) ) );

$warning = '';
$error = '';

if ( $tag->isInsideSubTreeLimit() )
    $warning = ezpI18n::tr( 'extension/eztags/warnings', 'TAKE CARE: Tag is inside class attribute subtree limit(s). If moved outside those limits, it could lead to inconsistency as objects could end up with tags that they are not supposed to have.' );

if ( $http->hasPostVariable( 'SaveButton' ) )
{
    $newKeyword = $http->hasPostVariable( 'TagEditKeyword' ) ? trim( $http->postVariable( 'TagEditKeyword' ) ) : '';
    if ( empty( $newKeyword ) )
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Name cannot be empty.' );

    if ( empty( $error ) )
    {
        $newParentID = $http->hasPostVariable( 'TagEditParentID' ) ? (int) $http->postVariable( 'TagEditParentID' ) : 0;
        $newParentTag = eZTagsObject::fetchWithMainTranslation( $newParentID );
        if ( !$newParentTag instanceof eZTagsObject && $newParentID > 0 )
            $error = ezpI18n::tr( 'extension/eztags/errors', 'Selected target tag is invalid.' );
    }

    // TODO: Multilanguage FIX
    if ( empty( $error ) && eZTagsObject::exists( $tag->attribute( 'id' ), $newKeyword, $newParentID ) )
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Tag/synonym with that name already exists in selected location.' );
    // END TODO

    if ( empty( $error ) )
    {
        $updateDepth = false;
        $updatePathString = false;

        $db = eZDB::instance();
        $db->begin();

        $oldParentDepth = $tag->attribute( 'depth' ) - 1;
        $newParentDepth = $newParentTag instanceof eZTagsObject ? $newParentTag->attribute( 'depth' ) : 0;

        if ( $oldParentDepth != $newParentDepth )
            $updateDepth = true;

        if ( $tag->attribute( 'parent_id' ) != $newParentID )
        {
            $oldParentTag = $tag->getParent( true );
            if ( $oldParentTag instanceof eZTagsObject )
                $oldParentTag->updateModified();

            $synonyms = $tag->getSynonyms( true );
            foreach ( $synonyms as $synonym )
            {
                $synonym->setAttribute( 'parent_id', $newParentID );
                $synonym->store();
            }

            $updatePathString = true;
        }

        $tagTranslation = $tag->translationByLocale( $language->attribute( 'locale' ) );
        if ( $tagTranslation instanceof eZTagKeyword )
        {
            $tagTranslation->setAttribute( 'keyword', $newKeyword );
            $tagTranslation->store();
        }
        else
        {
            $tagTranslation = new eZTagsKeyword( array( 'keyword_id'  => $tag->attribute( 'id' ),
                                                        'keyword'     => $newKeyword,
                                                        'language_id' => $language->attribute( 'id' ),
                                                        'locale'      => $language->attribute( 'locale' ) ) );

            $tagTranslation->store();
            $tag->updateLanguageMask();
        }

        $tag->setAlwaysAvailable( $http->hasPostVariable( 'AlwaysAvailable' ) );

        if ( $http->hasPostVariable( 'SetAsMainTranslation' ) )
            $tag->updateMainTranslation( $language->attribute( 'id' ) );

        $tag->setAttribute( 'parent_id', $newParentID );
        $tag->store();

        if ( $updatePathString )
            $tag->updatePathString();

        if ( $updateDepth )
            $tag->updateDepth();

        $tag->updateModified();
        $tag->registerSearchObjects();

        /* Extended Hook */
        if ( class_exists( 'ezpEvent', false ) )
            $tag = ezpEvent::getInstance()->filter( 'tag/edit', $tag );

        $db->commit();

        return $Module->redirectToView( 'id', array( $tag->attribute( 'id' ) ) );
    }
}

$tpl = eZTemplate::factory();

$tpl->setVariable( 'tag', $tag );
$tpl->setVariable( 'language', $language );
$tpl->setVariable( 'warning', $warning );
$tpl->setVariable( 'error', $error );

$Result = array();
$Result['content']    = $tpl->fetch( 'design:tags/edit.tpl' );
$Result['ui_context'] = 'edit';
$Result['path']       = eZTagsObject::generateModuleResultPath( $tag );

?>
