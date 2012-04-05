<div class="context-block tags-dashboard">
    <div class="box-header">
        <h1 class="context-title">{'Tags dashboard'|i18n( 'extension/eztags/tags/view' )}</h1>
        <div class="header-mainline"></div>
    </div>

    {if $show_reindex_message}
        <div class="message-warning">
            <h2>{'Manual search index regeneration is required for changes to be seen in search. Enable DelayedIndexing in site.ini to reindex automatically.'|i18n( 'extension/eztags/warnings' )}</h2>
        </div>
    {/if}

    <div class="box-content">
        <div id="window-controls" class="tab-block">
            {include uri='design:tags/window_controls.tpl'}
        </div>
    </div>
</div>

{if ezini( 'GeneralSettings', 'ShowOldStyleChildrenList', 'eztags.ini' )|eq( 'enabled' )}
    {include uri='design:eztags_children.tpl'}
{else}
    {include uri='design:eztags_children_yui.tpl'}
{/if}
