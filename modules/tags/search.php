<?php

$http = eZHTTPTool::instance();

$tagsSearchResults = array();
$tagsSearchCount = 0;
$offset = (isset($Params['Offset']) && is_numeric($Params['Offset']) && $Params['Offset'] > 0) ? (int) $Params['Offset'] : 0;
$limit = 15;
$viewParameters = array( 'offset' => $offset );

$tagsSearchText = '';
if($http->hasVariable('TagsSearchText'))
{
	$tagsSearchText = trim(urldecode($http->variable('TagsSearchText')));
}

$tagsSearchSubTree = 0;
if($http->hasVariable('TagsSearchSubTree') && is_numeric($http->variable('TagsSearchSubTree')) && $http->variable('TagsSearchSubTree') > 0)
{
	$tagsSearchSubTree = (int) $http->variable('TagsSearchSubTree');
}

$tagsIncludeSynonyms = false;
if($http->hasVariable('TagsIncludeSynonyms'))
{
	$tagsIncludeSynonyms = true;
}

if(strlen($tagsSearchText) > 0)
{
	$params = array('keyword' => array('like', '%' . $tagsSearchText . '%'));
	if($tagsSearchSubTree > 0)
	{
		$params['path_string'] = array('like', '%/' . $tagsSearchSubTree . '/%');
	}
	if(!$tagsIncludeSynonyms)
	{
		$params['main_tag_id'] = 0;
	}

	$limits = array('offset' => $offset);

	$tagsSearchCount = eZTagsObject::fetchListCount($params);
	$tagsSearchResults = eZTagsObject::fetchList($params, $limits);
}

$tpl = eZTemplate::factory();
$tpl->setVariable('tags_search_text', $tagsSearchText);
$tpl->setVariable('tags_search_subtree', $tagsSearchSubTree);
$tpl->setVariable('tags_include_synonyms', $tagsIncludeSynonyms);

$tpl->setVariable('tags_search_count', $tagsSearchCount);
$tpl->setVariable('tags_search_results', $tagsSearchResults);

$tpl->setVariable('view_parameters', $viewParameters);
$tpl->setVariable('persistent_variable', false);

$Result = array();
$Result['content'] = $tpl->fetch( 'design:tags/search.tpl' );
$Result['path'] = array( array( 'text' => ezpI18n::tr( 'extension/eztags/tags/search', 'Tags search' ),
                                'url' => false ) );

$contentInfoArray = array();
$contentInfoArray['persistent_variable'] = false;
if ( $tpl->variable( 'persistent_variable' ) !== false )
	$contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );

$Result['content_info'] = $contentInfoArray;

?>
