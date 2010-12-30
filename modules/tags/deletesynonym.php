<?php

$http = eZHTTPTool::instance();

$tagID = $Params['TagID'];

if ( is_numeric($tagID) && $tagID > 0 )
{
	$tag = eZTagsObject::fetch($tagID);
	if(!$tag)
	{
		return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
	}

	if($tag->MainTagID == 0)
	{
		return $Module->redirectToView( 'delete', array( $tagID ) );
	}

	if($http->hasPostVariable('NoButton'))
	{
		return $Module->redirectToView( 'id', array( $tagID ) );
	}
	else if($http->hasPostVariable('YesButton'))
	{
		$db = eZDB::instance();
		$db->begin();

		$parentTag = $tag->getParent();
		if($parentTag)
		{
			$parentTag->Modified = time();
			$parentTag->store();
		}

		$mainTag = $tag->getMainTag();
		$transferObjectsToMainTag = $http->hasPostVariable('TransferObjectsToMainTag');

		foreach($tag->TagAttributeLinks as $tagAttributeLink)
		{
			if($transferObjectsToMainTag && !$mainTag->isRelatedToObject($tagAttributeLink->ObjectAttributeID, $tagAttributeLink->ObjectID))
			{
				$tagAttributeLink->KeywordID = $tag->MainTagID;
				$tagAttributeLink->store();
			}
			else
			{
				$tagAttributeLink->remove();
			}
		}

		$tag->remove();

		$db->commit();

		if($parentTag)
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
		$Result['content'] = $tpl->fetch( 'design:tags/deletesynonym.tpl' );

		$Result['ui_context'] = 'edit';
		$Result['path'] = array( array( 'tag_id' => 0,
		                                'text' => ezpI18n::tr( 'extension/eztags/tags/edit', 'Delete synonym' ),
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
