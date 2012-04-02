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

if ( $tag->attribute( 'main_tag_id' ) != 0 )
{
    return $Module->redirectToView( 'merge', array( $tag->attribute( 'main_tag_id' ) ) );
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

            if ( $tag->attribute( 'parent_id' ) != $mainTag->attribute( 'parent_id' ) )
            {
                $oldParentTag = $tag->getParent();
                if ( $oldParentTag instanceof eZTagsObject )
                {
                    $oldParentTag->updateModified();
                }
            }

            /* Extended Hook */
            if ( class_exists( 'ezpEvent', false ) )
            {
                ezpEvent::getInstance()->filter( 'tag/merge', array(
                    'tag'          => $tag,
                    'newParentTag' => $mainTag,
                    'oldParentTag' => $oldParentTag ) );
            }

            eZTagsObject::moveChildren( $tag, $mainTag );

            $synonyms = $tag->getSynonyms();
            foreach ( $synonyms as $synonym )
            {
                $synonym->registerSearchObjects();
                foreach ( $synonym->getTagAttributeLinks() as $tagAttributeLink )
                {
                    $link = eZTagsAttributeLinkObject::fetchByObjectAttributeAndKeywordID(
                                $tagAttributeLink->attribute( 'objectattribute_id' ),
                                $tagAttributeLink->attribute( 'objectattribute_version' ),
                                $tagAttributeLink->attribute( 'object_id' ),
                                $mainTag->attribute( 'id' ) );

                    if ( !( $link instanceof eZTagsAttributeLinkObject ) )
                    {
                        $tagAttributeLink->setAttribute( 'keyword_id', $mainTag->attribute( 'id' ) );
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
                            $tagAttributeLink->attribute( 'objectattribute_id' ),
                            $tagAttributeLink->attribute( 'objectattribute_version' ),
                            $tagAttributeLink->attribute( 'object_id' ),
                            $mainTag->attribute( 'id' ) );

                if ( !( $link instanceof eZTagsAttributeLinkObject ) )
                {
                    $tagAttributeLink->setAttribute( 'keyword_id', $mainTag->attribute( 'id' ) );
                    $tagAttributeLink->store();
                }
                else
                {
                    $tagAttributeLink->remove();
                }
            }

            $tag->remove();

            $mainTag->updateModified();

            $db->commit();

            return $Module->redirectToView( 'id', array( $mainTag->attribute( 'id' ) ) );
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
