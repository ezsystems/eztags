{if $tag.main_tag_id|eq( 0 )}
    <h2>{'Subtree limitations'|i18n( 'extension/eztags/tags/view' )}</h2>

    {if $tag.subtree_limitations_count|gt( 0 )}
        <table class="list" cellpadding="0" border="0">
            <tbody>
                <tr>
                    <th class="tight">&nbsp;</th>
                    <th>{"Class ID"|i18n( "extension/eztags/tags/view" )}</th>
                    <th>{"Class name"|i18n( "extension/eztags/tags/view" )}</th>
                    <th>{"Attribute identifier"|i18n( "extension/eztags/tags/view" )}</th>
                </tr>
                {def $c = ''}
                {foreach $tag.subtree_limitations as $l}
                    {set $c = fetch( content, class, hash( class_id, $l.contentclass_id ) )}
                    <tr>
                        <td>{$c.identifier|class_icon( 'small', $c.name|wash )}</td>
                        <td>{$l.contentclass_id}</td>
                        <td><a href={concat( 'class/view/', $l.contentclass_id )|ezurl}>{$c.name|wash}</a></td>
                        <td>{$l.identifier}</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    {else}
        {"No subtree limitations"|i18n( "extension/eztags/tags/view" )}
    {/if}
{/if}