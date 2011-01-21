<?php

$http = eZHTTPTool::instance();

$mainTagID = $Params['MainTagID'];
$mainTag = eZTagsObject::fetch($mainTagID);

if ( is_numeric($mainTagID) && $mainTagID > 0 )
{
	if($mainTag->MainTagID != 0)
	{
		return $Module->redirectToView( 'addsynonym', array( $mainTag->MainTagID ) );
	}

	if($http->hasPostVariable('DiscardButton'))
	{
		return $Module->redirectToView( 'id', array( $mainTagID ) );
	}
	else if($http->hasPostVariable('SaveButton'))
	{
		if($http->hasPostVariable('TagEditKeyword') && strlen($http->postVariable( 'TagEditKeyword' )) > 0)
		{
			$currentTime = time();
			$parentTag = eZTagsObject::fetch($mainTag->ParentID);

			$db = eZDB::instance();
			$db->begin();

			if($parentTag)
			{
				$parentTag->Modified = $currentTime;
				$parentTag->store();
			}

			$tag = new eZTagsObject(array('parent_id' => $mainTag->ParentID,
										  'main_tag_id' => $mainTagID,
										  'keyword' => $http->postVariable( 'TagEditKeyword' ),
										  'path_string' => ($parentTag instanceof eZTagsObject) ? $parentTag->PathString : '/',
										  'modified' => $currentTime));

			$tag->store();
			$tag->PathString = $tag->PathString . $tag->ID . '/';
			$tag->store();

			$db->commit();

			return $Module->redirectToView( 'id', array( $tag->ID ) );
		}
		else
		{
			return $Module->redirectToView( 'addsynonym', array( $mainTagID ) );
		}
	}
	else
	{
		$tpl = eZTemplate::factory();

		$tpl->setVariable('main_tag', $mainTag);
		$tpl->setVariable('ui_context', 'edit');

		$Result = array();
		$Result['content'] = $tpl->fetch( 'design:tags/addsynonym.tpl' );
		$Result['ui_context'] = 'edit';
		$Result['path'] = array();

		$tempTag = $mainTag;
		while($tempTag->hasParent())
		{
			$Result['path'][] = array(  'tag_id' => $tempTag->ID,
			                            'text' => $tempTag->Keyword,
		                                'url' => false );
			$tempTag = $tempTag->getParent();
		}

		$Result['path'][] = array(  'tag_id' => $tempTag->ID,
		                            'text' => $tempTag->Keyword,
	                                'url' => false );

		$Result['path'] = array_reverse($Result['path']);

		$Result['path'][] = array(  'tag_id' => -1,
		                            'text' => ezpI18n::tr( 'extension/eztags/tags/edit', 'New synonym tag' ),
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