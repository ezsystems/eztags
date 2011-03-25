<?php

$http = eZHTTPTool::instance();

$tagID = $Params['TagID'];
$deleteAllowed = true;
$error = '';

if (!( is_numeric($tagID) && $tagID > 0 ))
{
	return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

$tag = eZTagsObject::fetch((int) $tagID);
if(!($tag instanceof eZTagsObject))
{
	return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

if($tag->MainTagID != 0)
{
	return $Module->redirectToView( 'delete', array( $tag->MainTagID ) );
}

if($tag->getSubTreeLimitationsCount() > 0)
{
	$deleteAllowed = false;
	$error = ezpI18n::tr('extension/eztags/errors', 'Tag cannot be modified because it is being used as subtree limitation in one or more class attributes.');
}
else
{
	if($http->hasPostVariable('NoButton'))
	{
		return $Module->redirectToView( 'id', array( $tagID ) );
	}

	if($http->hasPostVariable('YesButton'))
	{
		$db = eZDB::instance();
		$db->begin();

		$parentTag = $tag->getParent();
		if($parentTag instanceof eZTagsObject)
		{
			$parentTag->Modified = time();
			$parentTag->store();
		}

		eZTagsObject::recursiveTagDelete($tag);

		$db->commit();

		if($parentTag instanceof eZTagsObject)
		{
			return $Module->redirectToView( 'id', array( $parentTag->ID ) );
		}
		else
		{
			return $Module->redirectToView( 'dashboard', array() );
		}
	}
}

$tpl = eZTemplate::factory();

$tpl->setVariable('tag', $tag);
$tpl->setVariable('delete_allowed', $deleteAllowed);
$tpl->setVariable('error', $error);

$Result = array();
$Result['content'] = $tpl->fetch( 'design:tags/delete.tpl' );

$Result['ui_context'] = 'edit';
$Result['path'] = array( array( 'tag_id' => 0,
                                'text' => ezpI18n::tr( 'extension/eztags/tags/edit', 'Delete tag' ),
                                'url' => false ) );

$contentInfoArray = array();
$contentInfoArray['persistent_variable'] = false;
if ( $tpl->variable( 'persistent_variable' ) !== false )
	$contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );

$Result['content_info'] = $contentInfoArray;

?>
