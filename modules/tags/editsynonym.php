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

	if($tag->MainTagID == 0)
	{
		return $Module->redirectToView( 'edit', array( $tagID ) );
	}

	if($http->hasPostVariable('DiscardButton'))
	{
		return $Module->redirectToView( 'id', array( $tagID ) );
	}
	else if($http->hasPostVariable('SaveButton'))
	{
		if($http->hasPostVariable('TagEditKeyword') && strlen(trim($http->postVariable( 'TagEditKeyword' ))) > 0)
		{
			$currentTime = time();

			$db = eZDB::instance();
			$db->begin();

			$parentTag = $tag->getParent();
			if($parentTag instanceof eZTagsObject)
			{
				$parentTag->Modified = $currentTime;
				$parentTag->store();
			}

			$tag->Keyword = trim($http->postVariable( 'TagEditKeyword' ));
			$tag->Modified = $currentTime;
			$tag->store();

			$db->commit();

			return $Module->redirectToView( 'id', array( $tagID ) );
		}
		else
		{
			return $Module->redirectToView( 'editsynonym', array( $tagID ) );
		}
	}
	else
	{
		$tpl = eZTemplate::factory();

		$tpl->setVariable('tag', $tag);

		$Result = array();
		$Result['content'] = $tpl->fetch( 'design:tags/editsynonym.tpl' );
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
