{ezcss_require(array('jqmodal.css', 'contentstructure-tree.css'))}
{ezscript_require(array('jqModal.js', 'eztagsselectparent.js'))}

<div class="block"></div>

{* Subtree limit *}
<div class="block">
	<label>{'Limit by tags subtree'|i18n( 'design/standard/class/datatype' )}:</label>
	<input id="eztags_parent_id_{$class_attribute.id}" type="hidden" name="ContentClass_eztags_subtree_limit_{$class_attribute.id}" value="{$class_attribute.data_int1}" />
	<span id="eztags_parent_keyword_{$class_attribute.id}">{eztags_parent_string($class_attribute.data_int1)|wash(xhtml)}</span>
	<input class="button" type="button" name="SelectParentButton_{$class_attribute.id}" id="eztags-parent-selector-button-{$class_attribute.id}" value="{'Select subtree'|i18n( 'design/standard/class/datatype' )}" />
</div>

{* Tags addition enable/disable *}
<div class="block">
	<label><input type="checkbox" name="ContentClass_eztags_disable_addition_{$class_attribute.id}"{cond($class_attribute.data_int2|ne(0), ' checked="checked"', '')} /> {'Disable adding of new tags'|i18n( 'design/standard/class/datatype' )}</label>
</div>

{run-once}
	{include uri='design:ezjsctemplate/modal_dialog.tpl'}
{/run-once}