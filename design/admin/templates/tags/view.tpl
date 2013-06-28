<div class="context-block">
    <div class="box-header">
        <h1 class="context-title">
            <img class="transparent-png-icon" src={concat( 'tag_icons/normal/', $tag.icon )|ezimage} alt="{$tag.keyword|wash}" />
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
        {if $tag.main_tag_id|eq( 0 )}
            {include uri='design:parts/tags_view_control_bar.tpl' tag=$tag}
        {else}
            {include uri='design:parts/synonyms_view_control_bar.tpl' tag=$tag}
        {/if}

        <div class="block">
            {def $right_blocks = array()}

            <div class="left">
                {foreach $blocks as $block sequence array( 'left', 'right' ) as $position}
                    {if $position|eq( 'left' )}
                        {include uri=concat( 'design:tags/view/', $block, '.tpl' ) tag=$tag}
                    {else}
                        {append-block variable=$right_blocks}
                            {include uri=concat( 'design:tags/view/', $block, '.tpl' ) tag=$tag}
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