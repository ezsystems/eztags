<?php

$http = eZHTTPTool::instance();

$tagID = (int) $Params['TagID'];
$convertAllowed = true;
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
    return $Module->redirectToView( 'makesynonym', array( $tag->attribute( 'main_tag_id' ) ) );
}

if ( $tag->getSubTreeLimitationsCount() > 0 )
{
    $convertAllowed = false;
    $error = ezpI18n::tr( 'extension/eztags/errors', 'Tag cannot be modified because it is being used as subtree limitation in one or more class attributes.' );
}
else
{
    if ( $http->hasPostVariable( 'DiscardButton' ) )
    {
        return $Module->redirectToView( 'id', array( $tagID ) );
    }

    if ( $tag->isInsideSubTreeLimit() )
    {
        $warning = ezpI18n::tr( 'extension/eztags/warnings', 'TAKE CARE: Tag is inside class attribute subtree limit(s). If moved outside those limits, it could lead to inconsistency as objects could end up with tags that they are not supposed to have.' );
    }

    if ( $http->hasPostVariable( 'SaveButton' ) )
    {
        if ( !( $http->hasPostVariable( 'MainTagID' ) && (int) $http->postVariable( 'MainTagID' ) > 0 ) )
        {
            $error = ezpI18n::tr( 'extension/eztags/errors', 'Selected target tag is invalid.' );
        }

        if ( empty( $error ) )
        {
            $mainTag = eZTagsObject::fetch( (int) $http->postVariable( 'MainTagID' ) );
            if ( !( $mainTag instanceof eZTagsObject ) )
            {
                $error = ezpI18n::tr( 'extension/eztags/errors', 'Selected target tag is invalid.' );
            }
        }

        if ( empty( $error ) && eZTagsObject::exists( $tag->attribute( 'id' ), $tag->attribute( 'keyword' ), $mainTag->attribute( 'parent_id' ) ) )
        {
            $error = ezpI18n::tr( 'extension/eztags/errors', 'Tag/synonym with that name already exists in selected location.' );
        }

        if ( empty( $error ) )
        {
            $updateDepth = false;
            $updatePathString = false;

            $newParentTag = $mainTag->getParent();

            $db = eZDB::instance();
            $db->begin();

            if ( $tag->attribute( 'depth' ) != $mainTag->attribute( 'depth' ) )
                $updateDepth = true;

            if ( $tag->attribute( 'parent_id' ) != $mainTag->attribute( 'parent_id' ) )
            {
                $oldParentTag = $tag->getParent();
                if ( $oldParentTag instanceof eZTagsObject )
                {
                    $oldParentTag->updateModified();
                }

                $updatePathString = true;
            }

            eZTagsObject::moveChildren( $tag, $mainTag );

            $synonyms = $tag->getSynonyms();
            foreach ( $synonyms as $synonym )
            {
                $synonym->setAttribute( 'parent_id', $mainTag->attribute( 'parent_id' ) );
                $synonym->setAttribute( 'main_tag_id', $mainTag->attribute( 'id' ) );
                $synonym->store();
            }

            $tag->setAttribute( 'parent_id', $mainTag->attribute( 'parent_id' ) );
            $tag->setAttribute( 'main_tag_id', $mainTag->attribute( 'id' ) );
            $tag->store();

            if ( !$newParentTag instanceof eZTagsObject )
                $newParentTag = false;

            if ( $updatePathString )
                $tag->updatePathString( $newParentTag );

            if ( $updateDepth )
                $tag->updateDepth( $newParentTag );

            $tag->updateModified();

            $ini = eZINI::instance( 'eztags.ini' );
            if( $ini->variable( 'SearchSettings', 'IndexSynonyms' ) !== 'enabled' )
            {
                $tag->registerSearchObjects();
            }

            $db->commit();

            /* Extended Hook */
            if ( class_exists( 'ezpEvent', false ) )
                ezpEvent::getInstance()->filter( 'tag/makesynonym', array( 'tag' => $tag, 'mainTag' => $mainTag ) );

            return $Module->redirectToView( 'id', array( $tagID ) );
        }
    }
}

$tpl = eZTemplate::factory();

$tpl->setVariable( 'tag', $tag );
$tpl->setVariable( 'convert_allowed', $convertAllowed );
$tpl->setVariable( 'warning', $warning );
$tpl->setVariable( 'error', $error );

$Result = array();
$Result['content']    = $tpl->fetch( 'design:tags/makesynonym.tpl' );
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
