<?php

/** @var eZModule $Module */
/** @var array $Params */

$http = eZHTTPTool::instance();

$tagID = (int) $Params['TagID'];

$tag = eZTagsObject::fetchWithMainTranslation( $tagID );
if ( !$tag instanceof eZTagsObject )
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
<<<<<<< HEAD
}

if ( $tag->attribute( 'main_tag_id' ) == 0 )
{
    return $Module->redirectToView( 'delete', array( $tagID ) );
}
=======
>>>>>>> 06abc6e4d24cb0184dd64c8a211ac25dcafa5b1b

if ( $http->hasPostVariable( 'NoButton' ) )
    return $Module->redirectToView( 'id', array( $tag->attribute( 'id' ) ) );

if ( $tag->attribute( 'main_tag_id' ) == 0 )
    return $Module->redirectToView( 'delete', array( $tag->attribute( 'id' ) ) );

if ( $http->hasPostVariable( 'YesButton' ) )
{
    $db = eZDB::instance();
    $db->begin();

    $parentTag = $tag->getParent( true );
    if ( $parentTag instanceof eZTagsObject )
        $parentTag->updateModified();
<<<<<<< HEAD
    }

    $mainTagID = $tag->attribute( 'main_tag_id' );
=======
>>>>>>> 06abc6e4d24cb0184dd64c8a211ac25dcafa5b1b

    $tag->registerSearchObjects();

    if ( $http->hasPostVariable( 'TransferObjectsToMainTag' ) )
    {
        /* Extended Hook */
        if ( class_exists( 'ezpEvent', false ) )
        {
<<<<<<< HEAD
            $link = eZTagsAttributeLinkObject::fetchByObjectAttributeAndKeywordID(
                        $tagAttributeLink->attribute( 'objectattribute_id' ),
                        $tagAttributeLink->attribute( 'objectattribute_version' ),
                        $tagAttributeLink->attribute( 'object_id' ),
                        $mainTagID );

            if ( !( $link instanceof eZTagsAttributeLinkObject ) )
            {
                $tagAttributeLink->setAttribute( 'keyword_id', $mainTagID );
                $tagAttributeLink->store();
            }
            else
            {
                $tagAttributeLink->remove();
            }
        }

        /* Extended Hook */
        if ( class_exists( 'ezpEvent', false ) )
            ezpEvent::getInstance()->filter( 'tag/transferobjects', array( 'tag' => $tag, 'newTag' => $tag->getMainTag() ) );
=======
            ezpEvent::getInstance()->filter(
                'tag/transferobjects',
                array(
                    'tag' => $tag,
                    'newTag' => $tag->getMainTag()
                )
            );
        }

        $tag->transferObjectsToAnotherTag( $tag->attribute( 'main_tag_id' ) );
>>>>>>> 06abc6e4d24cb0184dd64c8a211ac25dcafa5b1b
    }

    /* Extended Hook */
    if ( class_exists( 'ezpEvent', false ) )
    {
        ezpEvent::getInstance()->filter( 'tag/delete', $tag );
    }

    /* Extended Hook */
    if ( class_exists( 'ezpEvent', false ) )
        ezpEvent::getInstance()->filter( 'tag/delete', $tag );

    $tag->remove();

    $db->commit();

    return $Module->redirectToView( 'id', array( $tag->attribute( 'main_tag_id' ) ) );
}

$tpl = eZTemplate::factory();

$tpl->setVariable( 'tag', $tag );

$Result = array();
$Result['content']    = $tpl->fetch( 'design:tags/deletesynonym.tpl' );
$Result['ui_context'] = 'edit';
$Result['path']       = eZTagsObject::generateModuleResultPath( false, null,
                                                                ezpI18n::tr( 'extension/eztags/tags/edit', 'Delete synonym' ) );
