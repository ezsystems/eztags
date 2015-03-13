<?php

/** @var eZModule $Module */
/** @var array $Params */

$http = eZHTTPTool::instance();

$mainTagID = (int) $Params['MainTagID'];

$locale = (string) $Params['Locale'];
if ( empty( $locale ) )
    $locale = $http->postVariable( 'Locale', false );

$mainTag = eZTagsObject::fetchWithMainTranslation( $mainTagID );
if ( !$mainTag instanceof eZTagsObject )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
<<<<<<< HEAD
}

if ( $mainTag->attribute( 'main_tag_id' ) != 0 )
{
    return $Module->redirectToView( 'addsynonym', array( $mainTag->attribute( 'main_tag_id' ) ) );
}
=======
>>>>>>> 06abc6e4d24cb0184dd64c8a211ac25dcafa5b1b

if ( $http->hasPostVariable( 'DiscardButton' ) )
    return $Module->redirectToView( 'id', array( $mainTag->attribute( 'id' ) ) );

if ( $mainTag->attribute( 'main_tag_id' ) != 0 )
    return $Module->redirectToView( 'addsynonym', array( $mainTag->attribute( 'main_tag_id' ) ) );

if ( $locale === false )
{
    /** @var eZContentLanguage[] $languages */
    $languages = eZContentLanguage::prioritizedLanguages();
    if ( !is_array( $languages ) || empty( $languages ) )
        return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

    if ( count( $languages ) == 1 )
        return $Module->redirectToView( 'addsynonym', array( $mainTag->attribute( 'id' ), $languages[0]->attribute( 'locale' ) ) );

    $tpl = eZTemplate::factory();

    $tpl->setVariable( 'languages', $languages );
    $tpl->setVariable( 'main_tag', $mainTag );
    $tpl->setVariable( 'ui_context', 'edit' );

    $Result = array();
    $Result['content']    = $tpl->fetch( 'design:tags/addsynonym_languages.tpl' );
    $Result['ui_context'] = 'edit';
    $Result['path']       = eZTagsObject::generateModuleResultPath( $mainTag, null,
                                                                    ezpI18n::tr( 'extension/eztags/tags/edit', 'New synonym tag' ) );

    return;
}

/** @var eZContentLanguage $language */
$language = eZContentLanguage::fetchByLocale( $locale );
if ( !$language instanceof eZContentLanguage )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

$error = '';

if ( $http->hasPostVariable( 'SaveButton' ) )
{
    $newKeyword = trim( $http->postVariable( 'TagEditKeyword', '' ) );
    if ( empty( $newKeyword ) )
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Name cannot be empty.' );

<<<<<<< HEAD
    $newKeyword = trim( $http->postVariable( 'TagEditKeyword' ) );
    if ( empty( $error ) && eZTagsObject::exists( 0, $newKeyword, $mainTag->attribute( 'parent_id' ) ) )
    {
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Tag/synonym with that name already exists in selected location.' );
    }

    if ( empty( $error ) )
    {
        $parentTag = eZTagsObject::fetch( $mainTag->attribute( 'parent_id' ) );
=======
    if ( empty( $error ) && eZTagsObject::exists( 0, $newKeyword, $mainTag->attribute( 'parent_id' ) ) )
        $error = ezpI18n::tr( 'extension/eztags/errors', 'Tag/synonym with that translation already exists in selected location.' );

    if ( empty( $error ) )
    {
        $parentTag = $mainTag->getParent( true );
>>>>>>> 06abc6e4d24cb0184dd64c8a211ac25dcafa5b1b

        $db = eZDB::instance();
        $db->begin();

<<<<<<< HEAD
        $tag = new eZTagsObject( array( 'parent_id'   => $mainTag->attribute( 'parent_id' ),
                                        'main_tag_id' => $mainTagID,
                                        'keyword'     => $newKeyword,
                                        'depth'       => $mainTag->attribute( 'depth' ),
                                        'path_string' => ( $parentTag instanceof eZTagsObject ) ? $parentTag->attribute( 'path_string' ) : '/' ) );
=======
        $languageMask = eZContentLanguage::maskByLocale( array( $language->attribute( 'locale' ) ), $http->hasPostVariable( 'AlwaysAvailable' ) );
>>>>>>> 06abc6e4d24cb0184dd64c8a211ac25dcafa5b1b

        $tag = new eZTagsObject( array( 'parent_id'        => $mainTag->attribute( 'parent_id' ),
                                        'main_tag_id'      => $mainTag->attribute( 'id' ),
                                        'depth'            => $mainTag->attribute( 'depth' ),
                                        'path_string'      => $parentTag instanceof eZTagsObject ? $parentTag->attribute( 'path_string' ) : '/',
                                        'main_language_id' => $language->attribute( 'id' ),
                                        'language_mask'    => $languageMask ), $language->attribute( 'locale' ) );
        $tag->store();
<<<<<<< HEAD
=======

        $translation = new eZTagsKeyword( array( 'keyword_id'  => $tag->attribute( 'id' ),
                                                 'language_id' => $language->attribute( 'id' ),
                                                 'keyword'     => $newKeyword,
                                                 'locale'      => $language->attribute( 'locale' ),
                                                 'status'      => eZTagsKeyword::STATUS_PUBLISHED ) );

        if ( $http->hasPostVariable( 'AlwaysAvailable' ) )
            $translation->setAttribute( 'language_id', $translation->attribute( 'language_id' ) + 1 );

        $translation->store();

>>>>>>> 06abc6e4d24cb0184dd64c8a211ac25dcafa5b1b
        $tag->setAttribute( 'path_string', $tag->attribute( 'path_string' ) . $tag->attribute( 'id' ) . '/' );
        $tag->store();
        $tag->updateModified();

        $db->commit();

        /* Extended Hook */
        if ( class_exists( 'ezpEvent', false ) )
        {
            ezpEvent::getInstance()->filter( 'tag/add', array( 'tag' => $tag, 'parentTag' => $parentTag ) );
            ezpEvent::getInstance()->filter( 'tag/makesynonym', array( 'tag' => $tag, 'mainTag' => $mainTag ) );
        }

        return $Module->redirectToView( 'id', array( $tag->attribute( 'id' ) ) );
    }
}

$tpl = eZTemplate::factory();

$tpl->setVariable( 'main_tag', $mainTag );
$tpl->setVariable( 'language', $language );
$tpl->setVariable( 'error', $error );
$tpl->setVariable( 'ui_context', 'edit' );

$Result = array();
$Result['content']    = $tpl->fetch( 'design:tags/addsynonym.tpl' );
$Result['ui_context'] = 'edit';
<<<<<<< HEAD
$Result['path']       = array();

$tempTag = $mainTag;
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

$Result['path'][] = array( 'tag_id' => -1,
                           'text'   => ezpI18n::tr( 'extension/eztags/tags/edit', 'New synonym tag' ),
                           'url'    => false );

$contentInfoArray = array();
$contentInfoArray['persistent_variable'] = false;
if ( $tpl->variable( 'persistent_variable' ) !== false )
    $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );

$Result['content_info'] = $contentInfoArray;

?>
=======
$Result['path']       = eZTagsObject::generateModuleResultPath( $mainTag, null,
                                                                ezpI18n::tr( 'extension/eztags/tags/edit', 'New synonym tag' ) );
>>>>>>> 06abc6e4d24cb0184dd64c8a211ac25dcafa5b1b
