<?php

$http = eZHTTPTool::instance();

$tagID = (int) $Params['TagID'];
$mergeAllowed = true;
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

if ( $tag->MainTagID != 0 )
{
    return $Module->redirectToView( 'merge', array( $tag->MainTagID ) );
}

if ( $tag->getSubTreeLimitationsCount() > 0 )
{
    $mergeAllowed = false;
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

        if ( empty( $error ) )
        {
            $db = eZDB::instance();
            $db->begin();

            if ( $tag->ParentID != $mainTag->ParentID )
            {
                $oldParentTag = $tag->getParent();
                if ( $oldParentTag instanceof eZTagsObject )
                {
                    $oldParentTag->updateModified();
                }
            }

            eZTagsObject::moveChildren( $tag, $mainTag );

            $synonyms = $tag->getSynonyms();
            foreach ( $synonyms as $synonym )
            {
                $synonym->registerSearchObjects();
                foreach ( $synonym->getTagAttributeLinks() as $tagAttributeLink )
                {
                    $link = eZTagsAttributeLinkObject::fetchByObjectAttributeAndKeywordID(
                                $tagAttributeLink->ObjectAttributeID,
                                $tagAttributeLink->ObjectAttributeVersion,
                                $tagAttributeLink->ObjectID,
                                $mainTag->ID );

                    if ( !( $link instanceof eZTagsAttributeLinkObject ) )
                    {
                        $tagAttributeLink->KeywordID = $mainTag->ID;
                        $tagAttributeLink->store();
                    }
                    else
                    {
                        $tagAttributeLink->remove();
                    }
                }

                $synonym->remove();
            }

            $tag->registerSearchObjects();
            foreach ( $tag->getTagAttributeLinks() as $tagAttributeLink )
            {
                $link = eZTagsAttributeLinkObject::fetchByObjectAttributeAndKeywordID(
                            $tagAttributeLink->ObjectAttributeID,
                            $tagAttributeLink->ObjectAttributeVersion,
                            $tagAttributeLink->ObjectID,
                            $mainTag->ID );

                if ( !( $link instanceof eZTagsAttributeLinkObject ) )
                {
                    $tagAttributeLink->KeywordID = $mainTag->ID;
                    $tagAttributeLink->store();
                }
                else
                {
                    $tagAttributeLink->remove();
                }
            }

            $tag->remove();

            $mainTag->updateModified();

            /* Extended Hook */
            if ( class_exists( 'ezpEvent', false ) )
                $tag = ezpEvent::getInstance()->filter( 'tag/merge', array( 'tag' => $tag, 'mainTag' => $mainTag ) );

            $db->commit();

            return $Module->redirectToView( 'id', array( $mainTag->ID ) );
        }
    }
}

$tpl = eZTemplate::factory();

$tpl->setVariable( 'tag', $tag );
$tpl->setVariable( 'merge_allowed', $mergeAllowed );
$tpl->setVariable( 'warning', $warning );
$tpl->setVariable( 'error', $error );

$Result = array();
$Result['content']    = $tpl->fetch( 'design:tags/merge.tpl' );
$Result['ui_context'] = 'edit';
$Result['path']       = array();

$tempTag = $tag;
while ( $tempTag->hasParent() )
{
    $tempTag = $tempTag->getParent();
    $Result['path'][] = array( 'tag_id' => $tempTag->ID,
                               'text'   => $tempTag->Keyword,
                               'url'    => false );
}

$Result['path'] = array_reverse( $Result['path'] );
$Result['path'][] = array( 'tag_id' => $tag->ID,
                           'text'   => $tag->Keyword,
                           'url'    => false );

$contentInfoArray = array();
$contentInfoArray['persistent_variable'] = false;
if ( $tpl->variable( 'persistent_variable' ) !== false )
    $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );

$Result['content_info'] = $contentInfoArray;

?>
