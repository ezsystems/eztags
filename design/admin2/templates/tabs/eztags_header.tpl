<li id="node-tab-eztags" class="{if $last}last{else}middle{/if}{if $node_tab_index|eq('eztags')} selected{/if}">
    {if $tabs_disabled}
        <span class="disabled">{'eZ Tags'|i18n( 'extension/eztags/node/view' )}</span>
    {else}
        <a href={concat( $node_url_alias, '/(tab)/eztags' )|ezurl} title="{'Show eZ Tags attributes overview.'|i18n( 'extension/eztags/node/view' )}">{'eZ Tags'|i18n( 'extension/eztags/node/view' )}</a>
    {/if}
</li>
