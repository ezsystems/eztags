{def $limit = 10}

<div class="tags-view extrainfo">
<div class="float-break" style="padding: 0 17em 0 0">
    <div class="main-column-position">
        <div class="main-column float-break">

            <div class="border-box">
            <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
            <div class="border-ml"><div class="border-mr"><div class="border-mc float-break">

            <div class="tag-header">
                <h1>
                <img class="transparent-png-icon" src={concat( 'tag_icons/normal/', $tag.icon )|ezimage} title="{$tag.keyword|wash}" alt="{$tag.keyword|wash}" />
                {$tag.keyword|wash}
                </h1>
            </div>

            {def $nodes = fetch( content, tree, hash( parent_node_id, 2,
                                                      extended_attribute_filter,
                                                      hash( id, TagsAttributeFilter,
                                                            params, hash( tag_id, $tag.id, include_synonyms, true() ) ),
                                                      offset, first_set( $view_parameters.offset, 0 ), limit, $limit,
                                                      main_node_only, true(),
                                                      sort_by, array( modified, false() ) ) )}

            {def $nodes_count = fetch( content, tree_count, hash( parent_node_id, 2,
                                                                  extended_attribute_filter,
                                                                  hash( id, TagsAttributeFilter,
                                                                        params, hash( tag_id, $tag.id, include_synonyms, true() ) ),
                                                                  main_node_only, true() ) )}

            {if $nodes|count}
                <div class="block">
                    <h2>{'Latest content'|i18n( 'extension/eztags/tags/view' )}</h2>
                    <ul>
                        {foreach $nodes as $node}
                            <li>{node_view_gui content_node=$node view=listitem}</li>
                        {/foreach}
                    </ul>
                </div>
            {/if}

            {include uri='design:navigator/google.tpl'
                     page_uri=concat( '/tags/view/', $tag.url )
                     item_count=$nodes_count
                     view_parameters=$view_parameters
                     item_limit=$limit}

            </div></div></div>
            <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
            </div>
        </div>
    </div>

    {if ezini( 'SearchSettings', 'SearchEngine', 'site.ini' )|eq( 'ezsolr' )}
        <div class="extrainfo-column-position">
            <div class="extrainfo-column">

                <div class="border-box">
                <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
                <div class="border-ml"><div class="border-mr"><div class="border-mc float-break">

                {def $nodes_related = fetch( ezfind, search, hash( limit, 0,
                                                                   filter, concat( 'ezf_df_tags:"', $tag.keyword, '"' ),
                                                                   facet, array( hash( field, 'ezf_df_tags', limit, 6 ) ) ) )}

                {if $nodes_related.SearchExtras.facet_fields.0.nameList|gt( 1 )}
                    <div class="block">
                        <h2>{'Related tags'|i18n( 'extension/eztags/tags/view' )}</h2>
                        <ul>
                            {def $t = false()}
                            {foreach $nodes_related.SearchExtras.facet_fields.0.nameList as $name sequence $nodes_related.SearchExtras.facet_fields.0.countList as $count}
                                {if $name|downcase|ne( $tag.keyword|downcase )}
                                    {set $t = fetch( tags, tags_by_keyword, hash( keyword, $name ) )}
                                    {if $t|count}
                                        <li><img class="transparent-png-icon" src={concat( 'tag_icons/small/', $t[0].icon )|ezimage} title="{$t[0].keyword|wash}" alt="{$t[0].keyword|wash}" /> <a href={concat( 'tags/view/', $t[0].url )|ezurl}>{$t[0].keyword|wash}</a> ({$count})</li>
                                    {/if}
                                {/if}
                            {/foreach}
                            {undef $t}
                        </ul>
                    </div>
                {/if}
                {undef $nodes_related}
                </div></div></div>
                <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
                </div>
            </div>
        </div>
    {/if}
</div>
</div>

{undef $limit $nodes $nodes_count}