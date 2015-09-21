{def $latest_tags = fetch( tags, latest_tags, hash( parent_tag_id, first_set( $tag.id, 0 ), limit, 10 ) )}

{if $latest_tags|count}
    <table class="list" cellpadding="0">
        <tbody>
            <tr>
                <th class="tight">&nbsp;</th>
                <th>{"ID"|i18n( "extension/eztags/tags/view" )}</th>
                <th>{"Tag name"|i18n( "extension/eztags/tags/view" )}</th>
                <th>{"Parent tag name"|i18n( "extension/eztags/tags/view" )}</th>
                <th>{"Modified"|i18n( "extension/eztags/tags/view" )}</th>
            </tr>
            {foreach $latest_tags as $t}
                <tr>
                    <td><img class="transparent-png-icon" src="{$t.icon|tag_icon}" alt="{$t.keyword|wash}" /></td>
                    <td>{$t.id}</td>
                    <td><a href={concat( 'tags/id/', $t.id )|ezurl}>{$t.keyword|wash}</a></td>
                    {if $t.parent}
                        <td><a href={concat( 'tags/id/', $t.parent.id )|ezurl}>{$t.parent.keyword|wash}</a></td>
                    {else}
                        <td>{"No parent"|i18n( "extension/eztags/tags/view" )}</td>
                    {/if}
                    <td>{$t.modified|datetime( 'custom', '%d.%m.%Y %H:%i' )}</td>
                </tr>
            {/foreach}
        </tbody>
    </table>
{else}
    {"No tags"|i18n( "extension/eztags/tags/view" )}
{/if}
