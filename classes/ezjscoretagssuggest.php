<?php

class ezjscoreTagsSuggest extends ezjscServerFunctions
{
	public static function autocomplete( $args )
	{
		$http = eZHTTPTool::instance();

		$searchString = $http->postVariable('search_string');

		$tags = eZTagsObject::fetchByKeyword(array('like', $searchString . '%'));

		$returnArray = array();
		$returnArray['status'] = 'success';
		$returnArray['message'] = '';
		$returnArray['tags'] = array();

		foreach($tags as $tag)
		{
			$returnArrayChild = array();
			$returnArrayChild['tag_parent_id'] = $tag->ParentID;
			$returnArrayChild['tag_parent_name'] = ($tag->hasParent()) ? $tag->getParent()->Keyword : '';
			$returnArrayChild['tag_name'] = $tag->Keyword;
			$returnArray['tags'][] = $returnArrayChild;
		}

		return $returnArray;
	}

	public static function suggest( $args )
	{
		$tags = array();
		$tagsCount = 1;
		$filteredTagsArray = array();
		$http = eZHTTPTool::instance();

		$tagsString = $http->postVariable('tags_string');
		$tagsArray = explode(',', $tagsString);

		if(count($tagsArray) > 0 && strlen(trim($tagsArray[0])) > 0)
		{
			$solrFilter = '"' . trim($tagsArray[0]) . '"';
			$filteredTagsArray[] = strtolower(trim($tagsArray[0]));
			for($i = 1; $i < count($tagsArray); $i++)
			{
				if(strlen(trim($tagsArray[$i])) > 0)
				{
					$solrFilter = $solrFilter . ' OR "' . trim($tagsArray[$i]) . '"';
					$filteredTagsArray[] = strtolower(trim($tagsArray[$i]));
					$tagsCount++;
				}
			}
			$solrFilter = 'article/tags:(' . $solrFilter . ')';

	        $solrSearch = new eZSolr();
	        $params = array( 'SearchOffset' => 0,
	                         'SearchLimit' => 0,
	                         'Facet' => array(array('field' => 'article/tags', 'limit' => 5 + $tagsCount, 'mincount', 1)),
	                         'SortBy' => null,
	                         'Filter' => $solrFilter,
	                         'SearchContentClassID' => null,
	                         'SearchSectionID' => null,
	                         'SearchSubTreeArray' => null,
	                         'AsObjects' => true,
	                         'SpellCheck' => null,
	                         'IgnoreVisibility' => false,
	                         'Limitation' => null,
	                         'BoostFunctions' => null,
	                         'QueryHandler' => 'ezpublish',
	                         'EnableElevation' => true,
	                         'ForceElevation' => false,
	                         'SearchDate' => null,
	                         'DistributedSearch' => null,
	                         'FieldsToReturn' => null );
	        $searchResult = $solrSearch->search( '', $params );
			$facetResult = $searchResult['SearchExtras']->attribute('facet_fields');
			$facetResult = $facetResult[0]['nameList'];

			$tags = array();
			foreach($facetResult as $facetValue)
			{
				if(!in_array(strtolower($facetValue), $filteredTagsArray))
				{
					$tags[] = trim($facetValue);
				}
			}

			if(count($tags) > 0)
			{
				$tags = eZTagsObject::fetchByKeyword(array($tags));
			}
		}

		$returnArray = array();
		$returnArray['status'] = 'success';
		$returnArray['message'] = '';
		$returnArray['tags'] = array();

		foreach($tags as $tag)
		{
			$returnArrayChild = array();
			$returnArrayChild['tag_parent_id'] = $tag->ParentID;
			$returnArrayChild['tag_parent_name'] = ($tag->hasParent()) ? $tag->getParent()->Keyword : '';
			$returnArrayChild['tag_name'] = $tag->Keyword;
			$returnArray['tags'][] = $returnArrayChild;
		}

		return $returnArray;
	}
}

?>