{ezcss_require( array( 'jqmodal.css', 'contentstructure-tree.css' ) )}
{ezscript_require( array( 'ezjsc::jquery', 'ezjsc::jqueryio', 'jqmodal.js', 'eztagsselectparent.js' ) )}

<div class="block"></div>

{* Subtree limit *}
<div class="block">
    <label>{'Limit by tags subtree'|i18n( 'design/standard/class/datatype' )}:</label>
    <input id="eztags_parent_id_{$class_attribute.id}" type="hidden" name="ContentClass_eztags_subtree_limit_{$class_attribute.id}" value="{$class_attribute.data_int1}" />
    <span id="eztags_parent_keyword_{$class_attribute.id}">{eztags_parent_string( $class_attribute.data_int1 )|wash}</span>
    <input class="button" type="button" name="SelectParentButton_{$class_attribute.id}" id="eztags-parent-selector-button-{$class_attribute.id}" value="{'Select subtree'|i18n( 'design/standard/class/datatype' )}" />
</div>

{* Hide root subtree limit tag when editing object *}
<div class="block">
    <label><input type="checkbox" name="ContentClass_eztags_hide_root_tag_{$class_attribute.id}"{cond( $class_attribute.data_int3|ne( 0 ), ' checked="checked"', '' )} /> {'Hide root subtree limit tag when editing object'|i18n( 'design/standard/class/datatype' )}</label>
</div>

{* Show dropdown instead of autocomplete *}
<div class="block">
    <label><input type="checkbox" name="ContentClass_eztags_show_dropdown_{$class_attribute.id}"{cond( $class_attribute.data_int2|ne( 0 ), ' checked="checked"', '' )} /> {'Show dropdown instead of autocomplete'|i18n( 'design/standard/class/datatype' )}</label>
</div>

{* Maximum number of allowed tags *}
<div class="block">
    <label>{'Maximum number of allowed tags'|i18n( 'design/standard/class/datatype' )}:</label> <input type="text" maxlength="5" size="5" name="ContentClass_eztags_max_tags_{$class_attribute.id}" value="{if $class_attribute.data_int4|gt( 0 )}{$class_attribute.data_int4}{else}0{/if}" />&nbsp;{'(0 = unlimited)'|i18n( 'design/standard/class/datatype' )}
</div>

{run-once}
    {include uri='design:ezjsctemplate/modal_dialog.tpl'}
{/run-once}