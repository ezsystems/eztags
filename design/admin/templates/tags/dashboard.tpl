<div class="context-block tags-dashboard">
    <div class="box-header">
        <h1 class="context-title">{'Tags Dashboard'|i18n( 'extension/eztags/tags/view' )}</h1>
        <div class="header-mainline"></div>
    </div>

    {if $show_reindex_message}
        <div class="message-warning">
            <h2>{'Manual search index regeneration is required for changes to be seen in search. Enable DelayedIndexing in site.ini to reindex automatically.'|i18n( 'extension/eztags/warnings' )}</h2>
        </div>
    {/if}

    <div class="box-content">
        <div id="controlbar-top" class="controlbar">
            <div class="box-bc"><div class="box-ml">
                <div class="button-left">
                    <form name="tagadd" id="tagadd" style="float:left;" enctype="multipart/form-data" method="post" action={'tags/add/0'|ezurl}>
                        <input class="defaultbutton" type="submit" name="SubmitButton" value="{"Add child tag"|i18n( "extension/eztags/tags/edit" )}" />
                    </form>
                </div>
                <div class="float-break"></div>
            </div></div>
        </div>

        <div class="block">
            {def $right_blocks = array()}

            <div class="left">
                {foreach $blocks as $block sequence array( 'left', 'right' ) as $position}
                    {if $position|eq( 'left' )}
                        <div class="dashboard-item">
                            {include uri=concat( 'design:tags/dashboard/', $block, '.tpl' )}
                        </div>
                    {else}
                        {append-block variable=$right_blocks}
                            <div class="dashboard-item">
                                {include uri=concat( 'design:tags/dashboard/', $block, '.tpl' )}
                            </div>
                        {/append-block}
                    {/if}
                {/foreach}
            </div>
            <div class="right">
                {$right_blocks|implode( '' )}
            </div>
            <div class="float-break"></div>
        </div>
    </div>
</div>

{include uri='design:eztags_children.tpl'}