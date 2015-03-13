<div class="context-block tags-edit">
    <div class="box-header">
        <h1 class="context-title">{'Move tags'|i18n( 'extension/eztags/tags/edit' )}</h1>
        <div class="header-mainline"></div>
    </div>

    <div class="box-content">
        <div class="block">
            <p>{'The following tags could not be moved because they contain translations that already exist in one of the tags in selected location.'|i18n( 'extension/eztags/errors' )}</p>
        </div>

        <div class="block">
            <table class="list" cellspacing="0">
                <tr>
                    <th colspan="2">{'Tag'|i18n( 'extension/eztags/tags/edit' )}</th>
                    <th>{'Related objects count'|i18n( 'extension/eztags/tags/edit' )}</th>
                    <th>{'Children count'|i18n( 'extension/eztags/tags/edit' )}</th>
                </tr>

                {foreach $unmovable_tags as $tag sequence array( 'bglight', 'bgdark' ) as $sequence}
                    <tr class="{$sequence}">
                        <td class="tight"><img class="transparent-png-icon" src="{$tag.icon|tag_icon}" alt="{$tag.keyword|wash}" /></td>
                        <td>{eztags_parent_string( $tag.id )|wash}</td>
                        <td>{$tag.related_objects_count|wash}</td>
                        <td>{$tag.children_count|wash}</td>
                    </tr>
                {/foreach}
            </table>
        </div>

        <div class="controlbar">
            <div class="block">
                {if $parent_tag_id|gt( 0 )}
                    <input class="defaultbutton" type="button" name="OkButton" value="OK"  onclick="window.location = '{concat( '/tags/id/', $parent_tag_id )|ezurl(no)}';" />
                {else}
                    <input class="defaultbutton" type="button" name="OkButton" value="OK"  onclick="window.location = '{'/tags/dashboard'|ezurl(no)}';" />
                {/if}
            </div>
        </div>
    </div>
</div>
