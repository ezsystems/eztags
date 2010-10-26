<?php

$http = eZHTTPTool::instance();

$TagID = $Params['TagID'];

if ( is_numeric($TagID) && $TagID >= 1 )
{
	$tag = eZTagsObject::fetch($TagID);
	if(!$tag)
	{
		return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
	}

	if($http->hasPostVariable('DiscardButton'))
	{
		return $Module->redirectToView( 'id', array( $TagID ) );
	}
	else if($http->hasPostVariable('SaveButton'))
	{
		if($http->hasPostVariable('TagEditKeyword') && strlen($http->postVariable( 'TagEditKeyword' )) > 0
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

				$tag->Keyword = $http->postVariable( 'TagEditKeyword' );
				$tag->ParentID = (int) $http->postVariable('TagEditParentID');
				$tag->Modified = $currentTime;
				$tag->store();

				$db->commit();

				return $Module->redirectToView( 'id', array( $TagID ) );
			}
			else
			{
				return $Module->redirectToView( 'edit', array( $TagID ) );
			}
		}
		else
		{
			return $Module->redirectToView( 'edit', array( $TagID ) );
		}
	}
	else
	{
		$tpl = eZTemplate::factory();

		$tpl->setVariable('id', $tag->ID);
		$tpl->setVariable('parent_id', $tag->ParentID);
		$tpl->setVariable('keyword', $tag->Keyword);

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
