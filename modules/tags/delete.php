<?php

$http = eZHTTPTool::instance();

$tagID = $Params['TagID'];

if ( is_numeric($tagID) && $tagID > 0 )
{
	$tag = eZTagsObject::fetch((int) $tagID);
	if(!($tag instanceof eZTagsObject))
	{
		return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
	}

	if($tag->MainTagID != 0)
	{
		return $Module->redirectToView( 'delete', array( $tag->MainTagID ) );
	}

	if($http->hasPostVariable('NoButton'))
	{
		return $Module->redirectToView( 'id', array( $tagID ) );
	}
	else if($http->hasPostVariable('YesButton'))
	{
		if($tag->getLockStatus() == eZTagsObject::LOCK_STATUS_HARD_LOCK)
		{
			return $Module->redirectToView( 'id', array( $tag->ID ) );
		}

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
	else
	{
		$tpl = eZTemplate::factory();

		$tpl->setVariable('tag', $tag);

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

		return $Result;
	}
}
else
{
	return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

?>
