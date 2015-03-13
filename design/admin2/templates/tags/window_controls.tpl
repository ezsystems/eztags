{def $tag_exists = is_set( $tag )}

{if $tag_exists}
    {def $tag_url                 = concat( 'tags/id/', $tag.id )
         $tab_index               = first_set( $view_parameters.tab, 'content' )
         $valid_tabs              = array( 'content', 'latest', 'translations', 'synonyms', 'limits', 'search' )
         $read_open_tab_by_cookie = true()
    }
{else}
    {def $tag_url                 = 'tags/dashboard'
         $tab_index               = first_set( $view_parameters.tab, 'latest' )
         $valid_tabs              = array( 'latest', 'search' )
         $read_open_tab_by_cookie = true()
    }
{/if}

{if $valid_tabs|contains( $tab_index )|not()}
    {set $tab_index = cond( $tag_exists, 'content', 'latest' )}
{elseif is_set( $view_parameters.tab )}
    {set $read_open_tab_by_cookie = false()}
{/if}

<ul class="tabs{if $read_open_tab_by_cookie} tabs-by-cookie{/if}">
    {if $tag_exists}
        <li id="node-tab-tags-content" class="first{if $tab_index|eq('content')} selected{/if}">
            <a href={concat( $tag_url, '/(tab)/content' )|ezurl}>{'Latest content'|i18n( 'extension/eztags/tags/view' )}</a>
        </li>
    {/if}

    <li id="node-tab-tags-latest" class="{if $tag_exists}middle{else}first{/if}{if $tab_index|eq('latest')} selected{/if}">
        <a href={concat( $tag_url, '/(tab)/latest' )|ezurl}>{'Latest tags'|i18n( 'extension/eztags/tags/view' )}</a>
    </li>

    {if $tag_exists}
        <li id="node-tab-tags-translations" class="middle{if $tab_index|eq('translations')} selected{/if}">
            <a href={concat( $tag_url, '/(tab)/translations' )|ezurl}>{'Tag translations'|i18n( 'extension/eztags/tags/view' )} ({$tag.translations_count})</a>
        </li>
    {/if}

    {if and( $tag_exists, $tag.main_tag_id|eq( 0 ) )}
        <li id="node-tab-tags-synonyms" class="middle{if $tab_index|eq('synonyms')} selected{/if}">
            <a href={concat( $tag_url, '/(tab)/synonyms' )|ezurl}>{'Synonyms'|i18n( 'extension/eztags/tags/view' )} ({$tag.synonyms_count})</a>
        </li>

        <li id="node-tab-tags-limits" class="middle{if $tab_index|eq('limits')} selected{/if}">
            <a href={concat( $tag_url, '/(tab)/limits' )|ezurl}>{'Subtree limitations'|i18n( 'extension/eztags/tags/view' )} ({$tag.subtree_limitations_count})</a>
        </li>
    {/if}

    {if fetch( user, has_access_to, hash( module, tags, function, search ) )}
        <li id="node-tab-tags-search" class="last{if $tab_index|eq('search')} selected{/if}">
            <a href={concat( $tag_url, '/(tab)/search' )|ezurl}>{'Tags search'|i18n( 'extension/eztags/tags/search' )}</a>
        </li>
    {/if}
</ul>
<div class="float-break"></div>

<div class="tabs-content">
    {include uri='design:tags/windows.tpl'}
</div>

{ezscript_require( 'node_tabs.js' )}
{undef}
