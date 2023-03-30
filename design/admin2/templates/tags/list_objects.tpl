{def $page_limit = 50}
{def $nodes_count = fetch( 'content', 'tree_count', hash( 'parent_node_id', 1,
                                          'extended_attribute_filter',
                                          hash( 'id', 'TagsAttributeFilter',
                                                'params', hash( 'tag_id', $tag.id, 'include_synonyms', false() ) ),
                                          'main_node_only', true(),
                                          'sort_by', array( 'modified', false() ) ) )}
{def $nodes = fetch( 'content', 'tree', hash( 'parent_node_id', 1,
                                          'extended_attribute_filter',
                                          hash( 'id', 'TagsAttributeFilter',
                                                'params', hash( 'tag_id', $tag.id, 'include_synonyms', false() ) ),
                                          'limit', $page_limit,
                                          'offset', $view_parameters.offset,
                                          'main_node_only', true(),
                                          'sort_by', array( 'modified', false() ) ) )}

<h2>List of content related to tag <a href={concat( 'tags/id/', $tag.id )|ezurl()}>{$tag.keyword|wash()}</a> ({$nodes_count}):</h2>
{if $nodes|count()}
    <table class="list" cellpadding="0">
        <tbody>
            <tr>
                <th>{"ID"|i18n( "extension/eztags/tags/view" )}</th>
                <th>{"Name"|i18n( "extension/eztags/tags/view" )}</th>
                <th>{"Modified"|i18n( "extension/eztags/tags/view" )}</th>
                <th>{"Class name"|i18n( "extension/eztags/tags/view" )}</th>
                <th>{"Visibility"|i18n( "extension/eztags/tags/view" )}</th>
            </tr>
            {foreach $nodes as $node}
                <tr>
                    <td>{$node.contentobject_id}</td>
                    <td><a href={$node.url_alias|ezurl()}>{$node.object.name|wash()}</a></td>
                    <td>{$node.object.modified|datetime( 'custom', '%d.%m.%Y %H:%i' )}</td>
                    <td>{$node.class_name|wash()}</td>
                    <td>
                        {if $node.is_invisible}
                            {if $node.is_hidden}
                                Hidden
                            {else}
                                Hidden by superior
                            {/if}
                        {else}
                            Visible
                        {/if}
                    </td>
                </tr>
            {/foreach}
        </tbody>
    </table>

    {include name=navigator
         uri='design:navigator/google.tpl'
         page_uri=concat( 'tags/list_objects/', $tag.id )
         item_count=$nodes_count
         view_parameters=$view_parameters
         item_limit=$page_limit}
{else}
    {"No content"|i18n( "extension/eztags/tags/view" )}
{/if}

{undef}