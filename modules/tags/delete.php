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

	if($http->hasPostVariable('NoButton'))
	{
		return $Module->redirectToView( 'id', array( $TagID ) );
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

		recursiveTagDelete($tag);

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

		$tpl->setVariable('id', $tag->ID);
		$tpl->setVariable('parent_id', $tag->ParentID);
		$tpl->setVariable('keyword', $tag->Keyword);

		$Result = array();
		$Result['content'] = $tpl->fetch( 'design:tags/delete.tpl' );

		$Result['ui_context'] = 'edit';
		$Result['path'] = array( array( 'tag_id' => 0,
		                                'text' => ezpI18n::tr( 'kernel/tags', 'Delete tag' ),
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

function recursiveTagDelete(eZTagsObject $rootTag)
{
	$children = eZTagsObject::fetchByParentID($rootTag->ID);

	foreach($children as $child)
	{
		recursiveTagDelete($child);
	}

	$tagAttributeLinks = eZTagsAttributeLinkObject::fetchByKeywordID($rootTag->ID);

	foreach($tagAttributeLinks as $tagAttributeLink)
	{
		$tagAttributeLink->remove();
	}

	$rootTag->remove();
}

?>
