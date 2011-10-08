<?php

$http = eZHTTPTool::instance();

$tagsSearchResults = array();
$tagsSearchCount = 0;

$offset = ( isset( $Params['Offset'] ) && (int) $Params['Offset'] > 0 ) ? (int) $Params['Offset'] : 0;
$limit = (int) eZINI::instance( 'eztags.ini' )->variable( 'SearchSettings', 'SearchLimit' );

$viewParameters = array( 'offset' => $offset );

$tagsSearchText = '';
if ( $http->hasVariable( 'TagsSearchText' ) )
    $tagsSearchText = trim( urldecode( $http->variable( 'TagsSearchText' ) ) );

$tagsSearchSubTree = 0;
if ( $http->hasVariable( 'TagsSearchSubTree' ) && (int) $http->variable( 'TagsSearchSubTree' ) > 0 )
    $tagsSearchSubTree = (int) $http->variable( 'TagsSearchSubTree' );

$tagsIncludeSynonyms = $http->hasVariable( 'TagsIncludeSynonyms' );

if ( !empty( $tagsSearchText ) )
{
    $limits = array( 'offset' => $offset, 'limit' => $limit );
    $params = array( 'keyword' => array( 'like', '%' . $tagsSearchText . '%' ) );
    $customFields = array( array( 'operation' => 'COUNT( * )', 'name' => 'row_count' ) );

    $customConds = null;
    if ( $tagsSearchSubTree > 0 )
    {
        if ( $tagsIncludeSynonyms )
            $customConds = ' AND ( path_string LIKE "%/' . $tagsSearchSubTree . '/%" OR main_tag_id = ' . $tagsSearchSubTree . ' ) ';
        else
            $params['path_string'] = array( 'like', '%/' . $tagsSearchSubTree . '/%' );
    }
    else if ( !$tagsIncludeSynonyms )
    {
        $params['main_tag_id'] = 0;
    }

    $tagsSearchResults = eZPersistentObject::fetchObjectList( eZTagsObject::definition(), null, $params, null, $limits, true, false, null, null, $customConds );
    $tagsSearchCount = eZPersistentObject::fetchObjectList( eZTagsObject::definition(), array(), $params, array(), null, false, false, $customFields, null, $customConds );
    $tagsSearchCount = $tagsSearchCount[0]['row_count'];
}

$tpl = eZTemplate::factory();
$tpl->setVariable( 'tags_search_text', $tagsSearchText );
$tpl->setVariable( 'tags_search_subtree', $tagsSearchSubTree );
$tpl->setVariable( 'tags_include_synonyms', $tagsIncludeSynonyms );

$tpl->setVariable( 'tags_search_count', $tagsSearchCount );
$tpl->setVariable( 'tags_search_results', $tagsSearchResults );

$tpl->setVariable( 'view_parameters', $viewParameters );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:tags/search.tpl' );
$Result['path']    = eZTagsObject::generateModuleResultPath( false, false, false,
                                                             ezpI18n::tr( 'extension/eztags/tags/search', 'Tags search' ) );

?>
