{if is_unset( $attribute_id )}{def $attribute_id = '0'}{/if}
{run-once}{include uri='design:ezjsctemplate/tree_menu_script.tpl'}{/run-once}

<div class="jqmDialog parent-selector-tree" id="parent-selector-tree-{$attribute_id}">
    <div class="jqmdIn">
        <div class="jqmdTC"><span class="jqmdTCLeft"></span><span class="jqDrag">{'Select tag'|i18n( 'extension/eztags/tags/treemenu' )}</span><span class="jqmdTCRight"></span></div>
        <div class="jqmdBL"><div class="jqmdBR"><div class="jqmdBC"><div class="jqmdBCIn">
            <div id="content-tree-{$attribute_id}">
                <div class="contentstructure">
                {if and( is_set( $root_tag ), $root_tag|is_array )}
                    {foreach $root_tag as $key => $value}
                        {include uri='design:ezjsctemplate/tree_menu.tpl' menu_persistence=false() attribute_id=concat( $attribute_id, '_', $key ) root_tag=$value}
                        {delimiter}<hr />{/delimiter}
                    {/foreach}
                {else}
                    {include uri='design:ezjsctemplate/tree_menu.tpl' menu_persistence=false() attribute_id=$attribute_id root_tag=cond( is_set( $root_tag ), $root_tag, false() )}
                {/if}
                </div>
            </div>
        </div></div></div></div>
        <a href="#" class="jqmdX jqmClose"></a>
    </div>
</div>