<h2>{'Tags search'|i18n( 'extension/eztags/tags/search' )}</h2>
<div class="searchblock">
    <form method="get" action={'tags/search'|ezurl}>
        <input id="tags_search_text" name="TagsSearchText" type="text" size="70" value="" />
        <input class="button" type="submit" name="TagsSearchButton" value="{"Search tags"|i18n( "extension/eztags/tags/search" )}" />
        <input type="hidden" value="{$tag.id}" name="TagsSearchSubTree" />
        <label for="tags_include_synonyms">
            <input type="checkbox" id="tags_include_synonyms" name="TagsIncludeSynonyms" checked="checked" /> {"Include synonyms in search"|i18n( "extension/eztags/tags/search" )}
        </label>
    </form>
</div>