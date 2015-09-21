{def $show_legacy_children_list = ezini( 'GeneralSettings', 'ShowOldStyleChildrenList', 'eztags.ini' )|eq( 'enabled' )}

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

        {if and( $show_legacy_children_list, fetch( user, has_access_to, hash( module, tags, function, add ) ) )}
            <div class="controlbar">
                <div class="button-left">
                    <div class="block">
                        <form name="tagadd" id="tagadd" style="float:left;" enctype="multipart/form-data" method="post" action={'tags/add/0'|ezurl}>
                            <input class="defaultbutton" type="submit" name="SubmitButton" value="{"Add child tag"|i18n( "extension/eztags/tags/view" )}" />
                        </form>
                    </div>
                </div>
                <div class="float-break"></div>
            </div>
        {/if}
    </div>
</div>

{if $show_legacy_children_list}
    {include uri='design:eztags_children.tpl'}
{else}
    {include uri='design:eztags_children_yui.tpl'}
{/if}

{undef $show_legacy_children_list}
