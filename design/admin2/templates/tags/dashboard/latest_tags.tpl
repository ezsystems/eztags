{def $latest_tags = fetch( tags, latest_tags, hash( limit, 10 ) )}

<h2>{'Latest tags'|i18n( 'extension/eztags/tags/dashboard' )}</h2>
<table class="list" cellpadding="0" border="0">
    <tbody>
        <tr>
            <th class="tight">&nbsp;</th>
            <th>{"ID"|i18n( "extension/eztags/tags/dashboard" )}</th>
            <th>{"Tag name"|i18n( "extension/eztags/tags/dashboard" )}</th>
            <th>{"Parent tag name"|i18n( "extension/eztags/tags/dashboard" )}</th>
            <th>{"Modified"|i18n( "extension/eztags/tags/dashboard" )}</th>
        </tr>
        {foreach $latest_tags as $latest_tag sequence array( 'bglight', 'bgdark' ) as $sequence}
            <tr>
                <td><img class="transparent-png-icon" src={concat( 'tag_icons/small/', $latest_tag.icon )|ezimage} alt="{$latest_tag.keyword|wash}" /></td>
                <td>{$latest_tag.id}</td>
                <td><a href={concat( 'tags/id/', $latest_tag.id )|ezurl}>{$latest_tag.keyword|wash}</a></td>
                {if $latest_tag.parent}
                    <td><a href={concat( 'tags/id/', $latest_tag.parent.id )|ezurl}>{$latest_tag.parent.keyword|wash}</a></td>
                {else}
                    <td>{"No parent"|i18n( "extension/eztags/tags/dashboard" )}</td>
                {/if}
                <td>{$latest_tag.modified|datetime( 'custom', '%d.%m.%Y %H:%i' )}</td>
            </tr>
        {/foreach}
    </tbody>
</table>
