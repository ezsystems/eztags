{def $item_limit = 15}

<form method="get" action={'tags/search'|ezurl}>
    <div class="context-block tags-search">
        <div class="box-header">
            <h1 class="context-title">
                {'Tags search'|i18n( 'extension/eztags/tags/search' )}
            </h1>
            <div class="header-mainline"></div>
        </div>

        <div class="box-content">
            <div class="context-attributes">
                <div class="block">
                    <input id="tags_search_text" name="TagsSearchText" type="text" size="100" value="{$tags_search_text}" />
                    <input class="button" type="submit" name="TagsSearchButton" value="{"Search tags"|i18n( "extension/eztags/tags/search" )}" />
                    <input type="hidden" name="TagsSearchSubTree" value="{$tags_search_subtree}" />
                    <label for="tags_include_synonyms">
                        <input type="checkbox" id="tags_include_synonyms" name="TagsIncludeSynonyms"{cond( $tags_include_synonyms, ' checked="checked"', '' )} /> {"Include synonyms in search"|i18n( "extension/eztags/tags/search" )}
                    </label>
                    <div class="float-break"></div>
                </div>

                {if $tags_search_text|count|eq( 0 )}
                    <h2>{'Empty search not allowed. Please enter your search query above.'|i18n( 'extension/eztags/tags/search' )}</h2>
                {elseif $tags_search_count|eq( 0 )}
                    <div class="block">
                        <h2>{'No tags were found while searching for "%1".'|i18n( 'extension/eztags/tags/search', , array( $tags_search_text|wash ) )}</h2>
                    </div>
                {/if}
            </div>
        </div>
    </div>

    {if and($tags_search_count|gt( 0 ), $tags_search_text|count)}
        <div class="context-block">
            <div class="box-header">
                <h2 class="context-title">
                    {'Search for "%1" returned %2 matches.'|i18n( 'extension/eztags/tags/search', , array( $tags_search_text|wash, $tags_search_count ) )}
                </h2>
                <div class="header-mainline"></div>
            </div>

            <div class="box-content">
                <table class="list" cellspacing="0">
                    <tbody>
                        <tr>
                            <th>{'Tag name'|i18n( 'extension/eztags/tags/search' )}</th>
                            <th>{'Parent tag'|i18n( 'extension/eztags/tags/search' )}</th>
                            <th>{'Main tag name'|i18n( 'extension/eztags/tags/search' )}</th>
                        </tr>

                        {foreach $tags_search_results as $result sequence array( 'bglight', 'bgdark' ) as $sequence}
                            <tr class="{$sequence}">
                                <td>
                                    <img class="transparent-png-icon" src={concat( 'tag_icons/small/', $result.icon )|ezimage} alt="{$result.keyword|wash}" />
                                    <a href={concat( 'tags/id/', $result.id )|ezurl}>{$result.keyword|wash}</a>
                                </td>
                                <td>{eztags_parent_string( $result.parent_id )}</td>
                                <td>
                                    {if $result.main_tag_id|gt( 0 )}
                                        <img class="transparent-png-icon" src={concat( 'tag_icons/small/', $result.main_tag.icon )|ezimage} alt="{$result.main_tag.keyword|wash}" />
                                        <a href={concat( 'tags/id/', $result.main_tag.id )|ezurl}>{$result.main_tag.keyword|wash}</a>
                                    {else}
                                        &nbsp;
                                    {/if}
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
                <div class="context-toolbar">
                    {include uri='design:navigator/google.tpl'
                             page_uri='/tags/search'
                             page_uri_suffix=concat( '?TagsSearchText=', $tags_search_text|urlencode, '&TagsSearchSubTree=', $tags_search_subtree, cond( $tags_include_synonyms, '&TagsIncludeSynonyms=on', '' ) )
                             item_count=$tags_search_count
                             view_parameters=$view_parameters
                             item_limit=$item_limit}
                </div>
            </div>
        </div>
    {/if}
</form>