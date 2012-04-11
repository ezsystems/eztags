<div class="context-block tags-delete">
    <div class="box-header">
        <h1 class="context-title">{"Delete tags"|i18n( 'extension/eztags/tags/edit' )}</h1>
        <div class="header-mainline"></div>
    </div>

    <div class="box-content">
        <form name="tagdeleteform" id="tagdeleteform" enctype="multipart/form-data" method="post" action={'/tags/deletetags'|ezurl}>
            <div class="block">
                <p>{'Are you sure you want to delete selected tags? All children tags and synonyms will also be deleted and removed from existing objects.'|i18n( 'extension/eztags/tags/edit' )}</p>
            </div>

            <table class="list" cellspacing="0">
                <tr>
                    <th colspan="2">{'Tag'|i18n( 'extension/eztags/tags/edit' )}</th>
                    <th>{'Related objects count'|i18n( 'extension/eztags/tags/edit' )}</th>
                    <th>{'Children count'|i18n( 'extension/eztags/tags/edit' )}</th>
                </tr>

                {foreach $tags as $tag sequence array( 'bglight', 'bgdark' ) as $sequence}
                    <tr class="{$sequence}">
                        <td class="tight"><img class="transparent-png-icon" src="{$tag.icon|tag_icon}" alt="{$tag.keyword|wash}" /></td>
                        <td>{eztags_parent_string( $tag.id )|wash}</td>
                        <td>{$tag.related_objects_count|wash}</td>
                        <td>{$tag.children_count|wash}</td>
                    </tr>
                {/foreach}
            </table>

            <div class="controlbar">
                <div class="block">
                    <input class="defaultbutton" type="submit" name="YesButton" value="{'Yes'|i18n( 'extension/eztags/tags/edit' )}" />
                    <input class="button" type="submit" name="NoButton" value="{'No'|i18n( 'extension/eztags/tags/edit' )}" />
                </div>
            </div>
        </form>
    </div>
</div>
