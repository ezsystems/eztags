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
        return $Module->redirectToView( 'editsynonym', array( $tag->attribute( 'id' ), $languages[0]->attribute( 'locale' ) ) );

    $tpl = eZTemplate::factory();

    $tpl->setVariable( 'tag', $tag );
    $tpl->setVariable( 'languages', $languages );

    $Result = array();
    $Result['content']    = $tpl->fetch( 'design:tags/editsynonym_languages.tpl' );
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

$language = eZContentLanguage::fetchByLocale( $locale );
if ( !$language instanceof eZContentLanguage )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

$tag = eZTagsObject::fetchByLocale( $tagID, $language->attribute( 'locale' ), true );
if ( !$tag instanceof eZTagsObject )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

if ( $tag->attribute( 'main_tag_id' ) == 0 )
    return $Module->redirectToView( 'edit', array( $tag->attribute( 'id' ) ) );

$error = '';

if ( $http->hasPostVariable( 'SaveButton' ) )
{
    $newKeyword = $http->hasPostVariable( 'TagEditKeyword' ) ? trim( $http->postVariable( 'TagEditKeyword' ) ) : '';
    if ( empty( $newKeyword ) )
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Name cannot be empty.' );

    // TODO: Multilanguage FIX
    if ( empty( $error ) && eZTagsObject::exists( $tag->attribute( 'id' ), $newKeyword, $tag->attribute( 'parent_id' ) ) )
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Tag/synonym with that name already exists in selected location.' );
    // END TODO

    if ( empty( $error ) )
    {
        $db = eZDB::instance();
        $db->begin();

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

        $tag->store();
        $tag->registerSearchObjects();

        $db->commit();

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
