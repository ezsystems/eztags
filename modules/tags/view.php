<?php

$params = $Params['Parameters'];

$tagName = end($params);

$tags = eZTagsObject::fetchByKeyword($tagName);

if(count($tags) > 0)
{
	$tpl = eZTemplate::factory();

	$tpl->setVariable('blocks', eZINI::instance('eztags.ini')->variable('View', 'ViewBlocks'));
	$tpl->setVariable( 'tag', $tags[0] );
	$tpl->setVariable( 'persistent_variable', false );

	$Result = array();
	$Result['content'] = $tpl->fetch( 'design:tags/view.tpl' );
	$Result['path'] = array();

	$tempTag = $tags[0];
	while($tempTag->hasParent())
	{
		$tempTag = $tempTag->getParent();
		$Result['path'][] = array(  'tag_id' => $tempTag->ID,
		                            'text' => $tempTag->Keyword,
	                                'url' => 'tags/view/' . urlencode($tempTag->Keyword) );
	}

	$Result['path'] = array_reverse($Result['path']);
	$Result['path'][] = array(  'tag_id' => $tags[0]->ID,
	                            'text' => $tags[0]->Keyword,
	                            'url' => false );

	$contentInfoArray = array();
	$contentInfoArray['persistent_variable'] = false;
	if ( $tpl->variable( 'persistent_variable' ) !== false )
		$contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );

	$Result['content_info'] = $contentInfoArray;
}
else
{
	return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}


?>
