<li id="node-tab-eztags" class="{if $last}last{else}middle{/if}{if $node_tab_index|eq('eztags')} selected{/if}">
    {if $tabs_disabled}
        <span class="disabled">{'eZ Tags'|i18n( 'design/admin/node/view/full' )}</span>
    {else}
        <a href={concat( $node_url_alias, '/(tab)/eztags' )|ezurl} title="{'Show eZ Tags overview.'|i18n( 'design/admin/node/view/full' )}">{'eZ Tags'|i18n( 'design/admin/node/view/full' )}</a>
    {/if}
</li>