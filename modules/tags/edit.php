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
		return $Module->redirectToView( 'edit', array( $tag->MainTagID ) );
	}

	$lockStatus = $tag->getLockStatus();

	if($http->hasPostVariable('DiscardButton'))
	{
		return $Module->redirectToView( 'id', array( $tagID ) );
	}
	else if($http->hasPostVariable('SaveButton'))
	{
		if($lockStatus != eZTagsObject::LOCK_STATUS_UNLOCKED)
		{
			if($http->hasPostVariable('TagEditKeyword') && strlen(trim($http->postVariable( 'TagEditKeyword' ))) > 0)
			{
				$db = eZDB::instance();
				$db->begin();

				$tag->Keyword = trim($http->postVariable( 'TagEditKeyword' ));
				$tag->Modified = time();
				$tag->store();

				$db->commit();

				return $Module->redirectToView( 'id', array( $tagID ) );
			}
			else
			{
				return $Module->redirectToView( 'edit', array( $tagID ) );
			}
		}
		else
		{
			if($http->hasPostVariable('TagEditKeyword') && strlen(trim($http->postVariable( 'TagEditKeyword' ))) > 0
				&& $http->hasPostVariable('TagEditParentID') && is_numeric($http->postVariable('TagEditParentID'))
				&& (int) $http->postVariable('TagEditParentID') >= 0)
			{
				$currentTime = time();
				$newParentTag = eZTagsObject::fetch((int) $http->postVariable('TagEditParentID'));
	
				if($newParentTag || (int) $http->postVariable('TagEditParentID') == 0)
				{
					$db = eZDB::instance();
					$db->begin();

					$oldParentTag = $tag->getParent();
					if($oldParentTag instanceof eZTagsObject)
					{
						$oldParentTag->Modified = $currentTime;
						$oldParentTag->store();
					}
	
					if($newParentTag instanceof eZTagsObject)
					{
						$newParentTag->Modified = $currentTime;
						$newParentTag->store();
					}
	
					$newParentID = (int) $http->postVariable('TagEditParentID');
	
					$synonyms = $tag->getSynonyms();
					foreach($synonyms as $synonym)
					{
						$synonym->ParentID = $newParentID;
						$synonym->Modified = $currentTime;
						$synonym->store();
					}
	
					$tag->Keyword = trim($http->postVariable( 'TagEditKeyword' ));
					$tag->ParentID = $newParentID;
					$tag->Modified = $currentTime;
					$tag->store();
					$tag->updatePathString(($newParentTag instanceof eZTagsObject) ? $newParentTag : false);
	
					$db->commit();
	
					return $Module->redirectToView( 'id', array( $tagID ) );
				}
				else
				{
					return $Module->redirectToView( 'edit', array( $tagID ) );
				}
			}
			else
			{
				return $Module->redirectToView( 'edit', array( $tagID ) );
			}
		}
	}
	else
	{
		$tpl = eZTemplate::factory();

		$tpl->setVariable('tag', $tag);

		$Result = array();
		$Result['content'] = $tpl->fetch( 'design:tags/edit.tpl' );
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
