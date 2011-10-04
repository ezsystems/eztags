<?php

$http = eZHTTPTool::instance();

$tagID = (int) $Params['TagID'];
$locale = trim( (string) $Params['Locale'] );

if ( strlen( $locale ) == 0 )
    $locale = $http->hasPostVariable( 'Locale' ) ? trim( $http->postVariable( 'Locale' ) ) : '';

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

if ( $tag->attribute( 'main_tag_id' ) != 0 )
{
    return $Module->redirectToView( 'edit', array( $tag->attribute( 'main_tag_id' ) ) );
}

if ( $http->hasPostVariable( 'DiscardButton' ) )
{
    return $Module->redirectToView( 'id', array( $tagID ) );
}

$language = eZContentLanguage::fetchByLocale( $locale );
if ( !$language instanceof eZContentLanguage )
{
    if ( strlen( $locale ) > 0 )
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Selected locale does not exist in the system. Please select a valid translation.' );

    $languageList = eZContentLanguage::fetchList();
    if ( is_array( $languageList ) && count( $languageList ) == 1 )
        return $Module->redirectToView( 'edit', array( $parentTagID, $languageList[0]->attribute( 'locale' ) ) );

    $tpl = eZTemplate::factory();

    $tpl->setVariable( 'tag', $tag );
    $tpl->setVariable( 'warning', $warning );
    $tpl->setVariable( 'error', $error );

    $languages = eZContentLanguage::fetchList();
    $tpl->setVariable( 'languages', $languages );

    $Result = array();
    $Result['content']    = $tpl->fetch( 'design:tags/edit_languages.tpl' );
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
    return;
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
    $newParentID = ( $newParentTag instanceof eZTagsObject ) ? $newParentTag->attribute( 'id' ) : 0;

    // TODO: Multilanguage FIX
    $newKeyword = trim( $http->postVariable( 'TagEditKeyword' ) );
    if ( empty( $error ) && eZTagsObject::exists( $tag->attribute( 'id' ), $newKeyword, $newParentID ) )
    {
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Tag/synonym with that name already exists in selected location.' );
    }
    // END TODO

    if ( empty( $error ) )
    {
        $updateDepth = false;
        $updatePathString = false;

        $db = eZDB::instance();
        $db->begin();

        $oldParentDepth = $tag->attribute( 'depth' ) - 1;
        $newParentDepth = ( $newParentTag instanceof eZTagsObject ) ? $newParentTag->attribute( 'depth' ) : 0;

        if ( $oldParentDepth != $newParentDepth )
            $updateDepth = true;

        if ( $tag->attribute( 'parent_id' ) != $newParentID )
        {
            $oldParentTag = $tag->getParent();
            if ( $oldParentTag instanceof eZTagsObject )
            {
                $oldParentTag->updateModified();
            }

            $synonyms = $tag->getSynonyms();
            foreach ( $synonyms as $synonym )
            {
                $synonym->setAttribute( 'parent_id', $newParentID );
                $synonym->store();
            }

            $updatePathString = true;
        }

        $tagTranslation = $tag->translationByLanguageID( $language->attribute( 'id' ) );
        if ( $tagTranslation instanceof eZTagKeyword )
        {
            $tagTranslation->setAttribute( 'keyword', $newKeyword );
            $tagTranslation->store();
        }
        else
        {
            $tagTranslation = new eZTagsKeyword( array(
                'keyword_id'  => $tag->attribute( 'id' ),
                'keyword'     => $newKeyword,
                'language_id' => $language->attribute( 'id' ),
                'locale'      => $language->attribute( 'locale' )
            ) );

            $tagTranslation->store();
            $tag->updateLanguageMask();
        }

        $tag->setAlwaysAvailable( $http->hasPostVariable( 'AlwaysAvailable' ) );

        if ( $http->hasPostVariable( 'SetAsMainTranslation' ) )
            $tag->updateMainTranslation( $language->attribute( 'id' ) );
        else if ( $language->attribute( 'id' ) == $tag->attribute( 'main_language_id' ) )
            $tag->setAttribute( 'keyword', $newKeyword );

        $tag->setAttribute( 'parent_id', $newParentID );
        $tag->store();

        if ( !$newParentTag instanceof eZTagsObject )
            $newParentTag = false;

        if ( $updatePathString )
            $tag->updatePathString( $newParentTag );

        if ( $updateDepth )
            $tag->updateDepth( $newParentTag );

        $tag->updateModified();
        $tag->registerSearchObjects();

        /* Extended Hook */
        if ( class_exists( 'ezpEvent', false ) )
            $tag = ezpEvent::getInstance()->filter( 'tag/edit', $tag );

        $db->commit();

        return $Module->redirectToView( 'id', array( $tagID ) );
    }
}

$tagTranslation = $tag->translationByLanguageID( $language->attribute( 'id' ) );
if ( $tagTranslation instanceof eZTagsKeyword )
    $tag->setAttribute( 'keyword', $tagTranslation->attribute( 'keyword' ) );
else
    $tag->setAttribute( 'keyword', '' );

$tpl = eZTemplate::factory();

$tpl->setVariable( 'tag', $tag );
$tpl->setVariable( 'locale', $locale );
$tpl->setVariable( 'language', $language );
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
