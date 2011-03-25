<?php

$http = eZHTTPTool::instance();

$parentTagID = $Params['ParentTagID'];
$error = '';
$parentTag = false;

if ( !(is_numeric($parentTagID) && $parentTagID >= 0) )
{
	return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

if($parentTagID > 0)
{
	$parentTag = eZTagsObject::fetch((int) $parentTagID);

	if(!($parentTag instanceof eZTagsObject))
	{
		return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
	}

	if($parentTag->MainTagID != 0)
	{
		return $Module->redirectToView( 'add', array( $parentTag->MainTagID ) );
	}
}

$userLimitations = eZTagsTemplateFunctions::getSimplifiedUserAccess('tags', 'add');
$hasAccess = false;

if(!isset($userLimitations['simplifiedLimitations']['Tag']))
{
	$hasAccess = true;
}
else
{
	$parentTagPathString = ($parentTag instanceof eZTagsObject) ? $parentTag->PathString : '/';
	foreach($userLimitations['simplifiedLimitations']['Tag'] as $key => $value)
	{
		if(strpos($parentTagPathString, '/' . $value . '/') !== false)
		{
			$hasAccess = true;
			break;
		}
	}
}

if(!$hasAccess)
{
	return $Module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' );
}

if($http->hasPostVariable('DiscardButton'))
{
	if($parentTag instanceof eZTagsObject)
		return $Module->redirectToView( 'id', array( $parentTagID ) );
	else
		return $Module->redirectToView( 'dashboard', array() );
}

if($http->hasPostVariable('SaveButton'))
{
	if(!($http->hasPostVariable('TagEditKeyword') && strlen(trim($http->postVariable( 'TagEditKeyword' ))) > 0))
	{
		$error = ezpI18n::tr('extension/eztags/errors', 'Name cannot be empty.');
	}

	if(empty($error) && !($http->hasPostVariable('TagEditParentID') && is_numeric($http->postVariable('TagEditParentID'))
		&& (int) $http->postVariable('TagEditParentID') >= 0))
	{
		$error = ezpI18n::tr('extension/eztags/errors', 'Selected target tag is invalid.');
	}

	if(empty($error))
	{
		$newParentTag = eZTagsObject::fetch((int) $http->postVariable('TagEditParentID'));
		if(!($newParentTag instanceof eZTagsObject || (int) $http->postVariable('TagEditParentID') == 0))
		{
			$error = ezpI18n::tr('extension/eztags/errors', 'Selected target tag is invalid.');
		}
	}

	if(empty($error))
	{
		$currentTime = time();

		$db = eZDB::instance();
		$db->begin();

		if($newParentTag instanceof eZTagsObject)
		{
			$newParentTag->Modified = $currentTime;
			$newParentTag->store();
		}

		$tag = new eZTagsObject(array('parent_id' => ($newParentTag instanceof eZTagsObject) ? $newParentTag->ID : 0,
									  'main_tag_id' => 0,
									  'keyword' => $http->postVariable( 'TagEditKeyword' ),
									  'path_string' => ($newParentTag instanceof eZTagsObject) ? $newParentTag->PathString : '/',
									  'modified' => $currentTime));

		$tag->store();
		$tag->PathString = $tag->PathString . $tag->ID . '/';
		$tag->store();

		$db->commit();

		return $Module->redirectToView( 'id', array( $tag->ID ) );
	}
}

$tpl = eZTemplate::factory();

$tpl->setVariable('parent_id', $parentTagID);
$tpl->setVariable('error', $error);
$tpl->setVariable('ui_context', 'edit');

$Result = array();
$Result['content'] = $tpl->fetch( 'design:tags/add.tpl' );
$Result['ui_context'] = 'edit';
$Result['path'] = array();

if($parentTag instanceof eZTagsObject)
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

?>