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

	if($tag->MainTagID != 0)
	{
		return $Module->redirectToView( 'makesynonym', array( $tag->MainTagID ) );
	}

	if($http->hasPostVariable('DiscardButton'))
	{
		return $Module->redirectToView( 'id', array( $tagID ) );
	}
	else if($http->hasPostVariable('SaveButton'))
	{
		if($tag->getLockStatus() == eZTagsObject::LOCK_STATUS_HARD_LOCK)
		{
			return $Module->redirectToView( 'id', array( $tag->ID ) );
		}

		if($http->hasPostVariable('MainTagID') && is_numeric($http->postVariable('MainTagID'))
			&& (int) $http->postVariable('MainTagID') > 0)
		{
			$currentTime = time();
			$mainTag = eZTagsObject::fetch((int) $http->postVariable('MainTagID'));
			$newParentTag = $mainTag->getParent();
			$oldParentTag = $tag->getParent();

			$db = eZDB::instance();
			$db->begin();

			if($oldParentTag)
			{
				$oldParentTag->Modified = $currentTime;
				$oldParentTag->store();
			}

			if($newParentTag)
			{
				$newParentTag->Modified = $currentTime;
				$newParentTag->store();
			}

			$synonyms = $tag->getSynonyms();
			foreach($synonyms as $synonym)
			{
				$synonym->ParentID = $mainTag->ParentID;
				$synonym->MainTagID = $mainTag->ID;
				$synonym->Modified = $currentTime;
				$synonym->store();
			}

			$tag->ParentID = $mainTag->ParentID;
			$tag->MainTagID = $mainTag->ID;
			$tag->Modified = $currentTime;
			$tag->store();
			$tag->updatePathString(($newParentTag instanceof eZTagsObject) ? $newParentTag : false);

			$db->commit();

			return $Module->redirectToView( 'id', array( $tagID ) );
		}
		else
		{
			return $Module->redirectToView( 'makesynonym', array( $tagID ) );
		}
	}
	else
	{
		$tpl = eZTemplate::factory();

		$tpl->setVariable('tag', $tag);

		$Result = array();
		$Result['content'] = $tpl->fetch( 'design:tags/makesynonym.tpl' );
		$Result['ui_context'] = 'edit';
		$Result['path'] = array();

		$tempTag = $tag;
		while($tempTag->hasParent())
		{
			$tempTag = $tempTag->getParent();
			$Result['path'][] = array(  'tag_id' => $tempTag->ID,
			                            'text' => $tempTag->Keyword,
		                                'url' => false );
		}

		$Result['path'] = array_reverse($Result['path']);
		$Result['path'][] = array(  'tag_id' => $tag->ID,
		                            'text' => $tag->Keyword,
		                            'url' => false );

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
