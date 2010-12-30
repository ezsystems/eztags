<?php

$http = eZHTTPTool::instance();

$parentTagID = $Params['ParentTagID'];

if ( is_numeric($parentTagID) && $parentTagID >= 0 )
{
	if($parentTagID > 0)
	{
		$parentTag = eZTagsObject::fetch($parentTagID);
		if($parentTag->MainTagID != 0)
		{
			return $Module->redirectToView( 'add', array( $parentTag->MainTagID ) );
		}
	}

	if($http->hasPostVariable('DiscardButton'))
	{
		if($parentTagID > 0)
			return $Module->redirectToView( 'id', array( $parentTagID ) );
		else
			return $Module->redirectToView( 'dashboard', array() );
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

				if($newParentTag)
				{
					$newParentTag->Modified = $currentTime;
					$newParentTag->store();
				}

				$tag = new eZTagsObject(array('parent_id' => (int) $http->postVariable('TagEditParentID'),
											  'main_tag_id' => 0,
											  'keyword' => $http->postVariable( 'TagEditKeyword' ),
											  'modified' => $currentTime));

				$tag->store();

				$db->commit();

				return $Module->redirectToView( 'id', array( $tag->ID ) );
			}
			else
			{
				return $Module->redirectToView( 'add', array( $parentTagID ) );
			}
		}
		else
		{
			return $Module->redirectToView( 'add', array( $parentTagID ) );
		}
	}
	else
	{
		$tpl = eZTemplate::factory();

		$tpl->setVariable('parent_id', $parentTagID);
		$tpl->setVariable('ui_context', 'edit');

		$Result = array();
		$Result['content'] = $tpl->fetch( 'design:tags/add.tpl' );
		$Result['ui_context'] = 'edit';
		$Result['path'] = array();

		if($parentTagID > 0)
		{
			$tempTag = $parentTag;
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
		}

		$Result['path'][] = array(  'tag_id' => -1,
		                            'text' => ezpI18n::tr( 'extension/eztags/tags/edit', 'New tag' ),
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