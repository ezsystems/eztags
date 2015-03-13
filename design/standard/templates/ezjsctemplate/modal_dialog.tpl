{if is_unset( $attribute_id )}{def $attribute_id = '0'}{/if}
{include uri='design:ezjsctemplate/tree_menu_script.tpl' menu_persistence=false()}

<div class="jqmDialog parent-selector-tree" id="parent-selector-tree-{$attribute_id}">
    <div class="jqmdIn">
        <div class="jqmdTC"><span class="jqmdTCLeft"></span><span class="jqDrag">{'Select tag'|i18n( 'extension/eztags/tags/treemenu' )}</span><span class="jqmdTCRight"></span></div>
        <div class="jqmdBL"><div class="jqmdBR"><div class="jqmdBC"><div class="jqmdBCIn">
            <div id="content-tree-{$attribute_id}">
                <div class="contentstructure">
                {if and( is_set( $root_tag ), $root_tag|is_array )}
                    {foreach $root_tag as $key => $value}
                        {include uri='design:ezjsctemplate/tree_menu.tpl' attribute_id=concat( $attribute_id, '_', $key ) root_tag=$value}
                        {delimiter}<hr />{/delimiter}
                    {/foreach}
                {else}
                    {include uri='design:ezjsctemplate/tree_menu.tpl' attribute_id=$attribute_id root_tag=cond( is_set( $root_tag ), $root_tag, false() )}
                {/if}
                </div>
            </div>
        </div></div></div></div>
        <a href="#" class="jqmdX jqmClose"></a>
    </div>
</div>
<<<<<<< HEAD
=======

<script type="text/javascript">
$('#parent-selector-tree-{$attribute_id}').jqm({ldelim}modal:true, overlay:60, overlayClass:'whiteOverlay'{rdelim}).jqDrag('.jqDrag');
</script>
>>>>>>> 06abc6e4d24cb0184dd64c8a211ac25dcafa5b1b
