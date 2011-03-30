{if $tag.main_tag_id|eq( 0 )}
    <h2>{'Synonyms'|i18n( 'extension/eztags/tags/view' )}</h2>

    {if $tag.synonyms_count|gt( 0 )}
        <table class="list" cellpadding="0" border="0">
            <tbody>
                <tr>
                    <th class="tight">&nbsp;</th>
                    <th>{"ID"|i18n( "extension/eztags/tags/view" )}</th>
                    <th>{"Name"|i18n( "extension/eztags/tags/view" )}</th>
                    <th>{"Modified"|i18n( "extension/eztags/tags/view" )}</th>
                </tr>
                {foreach $tag.synonyms as $synonym}
                    <tr>
                        <td><img class="transparent-png-icon" src={concat( 'tag_icons/small/', $synonym.icon )|ezimage} alt="{$synonym.keyword|wash}" /></td>
                        <td>{$synonym.id}</td>
                        <td><a href={concat( 'tags/id/', $synonym.id )|ezurl}>{$synonym.keyword|wash}</a></td>
                        <td>{$synonym.modified|datetime( 'custom', '%d.%m.%Y %H:%i' )}</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    {else}
        {"No synonyms"|i18n( "extension/eztags/tags/view" )}
    {/if}
{/if}