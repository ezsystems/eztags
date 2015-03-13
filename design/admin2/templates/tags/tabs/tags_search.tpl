<div class="block">
    <fieldset>
        <legend>{'Tags search'|i18n( 'extension/eztags/tags/search' )}</legend>
        <form method="get" action={'tags/search'|ezurl}>
            <input id="tags_search_text" name="TagsSearchText" type="text" size="70" value="" />
            <input class="button" type="submit" name="TagsSearchButton" value="{"Search tags"|i18n( "extension/eztags/tags/search" )}" />
            {if is_set( $tag.id )}
                <input type="hidden" value="{$tag.id}" name="TagsSearchSubTree" />
            {else}
                <input type="hidden" value="0" name="TagsSearchSubTree" />
            {/if}
            <label for="tags_include_synonyms">
                <input type="checkbox" id="tags_include_synonyms" name="TagsIncludeSynonyms" checked="checked" /> {"Include synonyms in search"|i18n( "extension/eztags/tags/search" )}
            </label>
        </form>
    </fieldset>
</div>
