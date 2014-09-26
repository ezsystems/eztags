{* Template to display the latest content related to the current tag *}
{def
    $number_of_items = 10
    $nodes = fetch( "content", "tree", hash(
        "parent_node_id",            2,
        "extended_attribute_filter", hash( id, TagsAttributeFilter, params, hash( tag_id, $tag.id, include_synonyms, false() ) ),
        "main_node_only",            true(),
        "limit",                     $number_of_items,
        "offset",                    $view_parameters.custom_offset,
        "sort_by",                   array( modified, false() )
    ) )
    $nodes_count = fetch( "content", "tree_count", hash(
        "parent_node_id",            2,
        "extended_attribute_filter", hash( id, TagsAttributeFilter, params, hash( tag_id, $tag.id, include_synonyms, false() ) ),
        "main_node_only",            true(),
    ) )
}
<h2>{'Latest content'|i18n( 'extension/eztags/tags/view' )} {if $nodes_count|gt(0)}{$view_parameters.custom_offset|sum(1)}-{$nodes|count()|sum($view_parameters.custom_offset)} of {$nodes_count}{/if}</h2>

{if $nodes|count()}
    <table class="list" cellpadding="0" border="0">
        <tbody>
            <tr>
                <th>{"ID"|i18n( "extension/eztags/tags/view" )}</th>
                <th>{"Name"|i18n( "extension/eztags/tags/view" )}</th>
                <th>{"Modified"|i18n( "extension/eztags/tags/view" )}</th>
                <th>{"Class name"|i18n( "extension/eztags/tags/view" )}</th>
            </tr>
            {foreach $nodes as $node}
                <tr>
                    <td>{$node.contentobject_id}</td>
                    <td><a href={$node.url_alias|ezurl()}>{$node.object.name|wash()}</a></td>
                    <td>{$node.object.modified|datetime( 'custom', '%d.%m.%Y %H:%i' )}</td>
                    <td>{$node.class_name|wash()}</td>
                </tr>
            {/foreach}
        </tbody>
        <tfoot>
            <tr><td colspan="4" style="background-color:#f5f5f5">
            {include
                name                  = navigator
                uri                   = 'design:navigator/eztags_google.tpl'
                page_uri              = concat('tags/id/', $tag.id)|ezurl("no")
                item_count            = $nodes_count
                view_parameters       = $view_parameters
                item_limit            = $number_of_items
                show_google_navigator = true()
                custom_offset         = true()
            }
            </td></tr>
        </tfoot>
    </table>
{else}
    {"No content"|i18n( "extension/eztags/tags/view" )}
{/if}

{undef}
