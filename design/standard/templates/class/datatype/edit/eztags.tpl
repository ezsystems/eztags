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

{run-once}
	{include uri='design:ezjsctemplate/modal_dialog.tpl'}
{/run-once}