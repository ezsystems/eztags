{def $latest_tags = fetch( tags, latest_tags, hash( parent_tag_id, $tag.id, limit, 10 ) )}

<h2>{'Latest tags'|i18n( 'extension/eztags/tags/dashboard' )}</h2>

{if $latest_tags|count}
    <table class="list" cellpadding="0" border="0">
        <tbody>
            <tr>
                <th class="tight">&nbsp;</th>
                <th>{"ID"|i18n( "extension/eztags/tags/dashboard" )}</th>
                <th>{"Tag name"|i18n( "extension/eztags/tags/dashboard" )}</th>
                <th>{"Parent tag name"|i18n( "extension/eztags/tags/dashboard" )}</th>
                <th>{"Modified"|i18n( "extension/eztags/tags/dashboard" )}</th>
            </tr>
            {foreach $latest_tags as $t sequence array( 'bglight', 'bgdark' ) as $sequence}
                <tr>
                    <td><img class="transparent-png-icon" src={concat( 'tag_icons/small/', $t.icon )|ezimage} alt="{$t.keyword|wash}" /></td>
                    <td>{$t.id}</td>
                    <td><a href={concat( 'tags/id/', $t.id )|ezurl}>{$t.keyword|wash}</a></td>
                    {if $t.parent}
                        <td><a href={concat( 'tags/id/', $t.parent.id )|ezurl}>{$t.parent.keyword|wash}</a></td>
                    {else}
                        <td>{"No parent"|i18n( "extension/eztags/tags/dashboard" )}</td>
                    {/if}
                    <td>{$t.modified|datetime( 'custom', '%d.%m.%Y %H:%i' )}</td>
                </tr>
            {/foreach}
        </tbody>
    </table>
{else}
    {"No content"|i18n( "extension/eztags/tags/view" )}
{/if}