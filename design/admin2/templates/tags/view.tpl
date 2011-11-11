<div class="context-block">
    <div class="box-header">
        <h1 class="context-title">
            <img class="transparent-png-icon" src="{$tag.icon|tag_icon( 'normal' )}" alt="{$tag.keyword|wash}" />
            {if $tag.main_tag_id|eq( 0 )}
                {'Tag'|i18n( 'extension/eztags/tags/view' )}: {$tag.keyword|wash}
            {else}
                {'Synonym'|i18n( 'extension/eztags/tags/view' )}: {$tag.keyword|wash} ({'Main tag'|i18n( 'extension/eztags/tags/view' )}: <a href={concat( 'tags/id/', $tag.main_tag_id )|ezurl}>{$tag.main_tag.keyword|wash}</a>)
            {/if}
        </h1>
        <div class="header-mainline"></div>
    </div>

    {if $show_reindex_message}
        <div class="message-warning">
            <h2>{'Manual search index regeneration is required for changes to be seen in search. Enable DelayedIndexing in site.ini to reindex automatically.'|i18n( 'extension/eztags/warnings' )}</h2>
        </div>
    {/if}

    <div class="box-content">
        <div class="context-information">
            <p class="left modified">{'Last modified'|i18n( 'extension/eztags/tags/view' )}: {$tag.modified|l10n(shortdatetime)} ({'Tag ID'|i18n( 'extension/eztags/tags/view' )}: {$tag.id})</p>
            <p class="right translation">{$tag.language_name_array[$tag.current_language]|wash}&nbsp;<img src="{$tag.current_language|flag_icon}" width="18" height="12" alt="{$tag.current_language|wash}" style="vertical-align: middle;" /></p>
            <div class="break"></div>
        </div>

        <div id="window-controls" class="tab-block">
            {include uri='design:tags/window_controls.tpl'}
        </div>

        {if $tag.main_tag_id|eq( 0 )}
            {include uri='design:parts/tags_view_control_bar.tpl' tag=$tag}
        {else}
            {include uri='design:parts/synonyms_view_control_bar.tpl' tag=$tag}
        {/if}
    </div>
</div>

{if ezini( 'GeneralSettings', 'ShowOldStyleChildrenList', 'eztags.ini' )|eq( 'enabled' )}
    {include uri='design:eztags_children.tpl'}
{else}
    {include uri='design:eztags_children_yui.tpl'}
{/if}
