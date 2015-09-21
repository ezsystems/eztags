{def $limit = 10}

<section class="content-view-full">
    <div class="class-blog">
        <div class="row">
            <div class="span8">
                <div class="attribute-header">
                    <h1><img class="transparent-png-icon" src="{$tag.icon|tag_icon( 'normal' )}" title="{$tag.keyword|wash}" alt="{$tag.keyword|wash}" style="margin-right:10px" />{$tag.keyword|wash}</h1>
                </div>

                {def $nodes = fetch( content, tree,
                    hash(
                        parent_node_id, 2,
                        extended_attribute_filter, hash(
                            id, TagsAttributeFilter,
                            params, hash(
                                tag_id, $tag.id,
                                include_synonyms, true()
                            )
                        ),
                        offset, first_set( $view_parameters.offset, 0 ),
                        limit, $limit,
                        main_node_only, true(),
                        sort_by, array( modified, false() )
                    )
                )}

                {def $nodes_count = fetch( content, tree_count,
                    hash(
                        parent_node_id, 2,
                        extended_attribute_filter, hash(
                            id, TagsAttributeFilter,
                            params, hash(
                                tag_id, $tag.id,
                                include_synonyms, true()
                            )
                        ),
                        main_node_only, true()
                    )
                )}

                {if $nodes|count}
                    <section class="content-view-children">
                        {foreach $nodes as $node}
                            {node_view_gui content_node=$node view=line}
                        {/foreach}
                    </section>
                {/if}

                {include
                    uri='design:navigator/google.tpl'
                    page_uri=$tag.url
                    item_count=$nodes_count
                    view_parameters=$view_parameters
                    item_limit=$limit
                }
            </div>

            <div class="span4">
                <aside>
                    <section class="content-view-aside">
                        {def $related_nodes = fetch( ezfind, search,
                            hash(
                                limit, 0,
                                filter, concat( 'ezf_df_tag_ids:"', $tag.id, '"' ),
                                facet, array( hash( field, 'ezf_df_tag_ids', limit, 11 ) )
                            )
                        )}

                        {if $related_nodes.SearchExtras.facet_fields.0.nameList|gt( 1 )}
                            {def $related_tags = fetch( tags, tag,
                                hash(
                                    tag_id, $related_nodes.SearchExtras.facet_fields.0.nameList
                                )
                            )}

                            {if $related_tags|count}
                                <h2>{'Related tags'|i18n( 'extension/eztags/tags/view' )}</h2>

                                <article>
                                    <div class="attribute-tags">
                                        <ul>
                                            {foreach $related_tags as $related_tag}
                                                {if $related_tag.id|ne( $tag.id )}
                                                    <li>
                                                        <img class="transparent-png-icon" src="{$related_tag.icon|tag_icon}" title="{$related_tag.keyword|wash}" alt="{$related_tag.keyword|wash}" style="margin-right:5px" />
                                                        <a href={$related_tag.url|ezurl}>{$related_tag.keyword|wash} ({$related_nodes.SearchExtras.facet_fields.0.countList[$related_tag.id]})</a>
                                                    </li>
                                                {/if}
                                            {/foreach}
                                        </ul>
                                    </div>
                                </article>
                            {/if}

                            {undef $related_tags}
                        {/if}

                        {undef $related_nodes}
                    </section>
                </aside>
            </div>
        </div>
    </div>
</section>

{undef $nodes $nodes_count}
