{ezcss_require(array('jqmodal.css', 'contentstructure-tree.css'))}
{ezscript_require(array('jqModal.js', 'eztagsselectparent.js'))}

<div class="context-block tags-add">
	<div class="box-header">
		<h1 class="context-title">{"New tag"|i18n('extension/eztags/tags/edit')}</h1>
		<div class="header-mainline"></div>
	</div>

	<div class="box-content">
		<form name="tageditform" id="tageditform" enctype="multipart/form-data" method="post" action={concat('tags/add/', $parent_id)|ezurl}>
			<div class="block tag-edit-keyword">
				<label>{'Tag name'|i18n( 'extension/eztags/tags/edit' )}</label>
				<input id="keyword" class="halfbox" type="text" size="70" name="TagEditKeyword" value="" />
			</div>

			<div class="block tag-edit-parent">
				<label>{'Parent tag'|i18n( 'extension/eztags/tags/edit' )}</label>
				<input id="eztags_parent_id_0" type="hidden" name="TagEditParentID" value="{$parent_id}" />
				<input id="hide_tag_id_0" type="hidden" name="TagHideID" value="-1" />
				<span id="eztags_parent_keyword_0">{eztags_parent_string($parent_id)|wash(xhtml)}</span>
				<input class="button" type="button" name="SelectParentButton" id="eztags-parent-selector-button-0" value="{'Select parent'|i18n( 'extension/eztags/tags/edit' )}" />
			</div>

			<div class="controlbar">
				<div class="block">
					<input class="defaultbutton" type="submit" name="SaveButton" value="{'Save'|i18n( 'extension/eztags/tags/edit' )}" />
					<input class="button" type="submit" name="DiscardButton" value="{'Discard'|i18n( 'extension/eztags/tags/edit' )}" onclick="return confirmDiscard( '{'Are you sure you want to discard changes?'|i18n( 'extension/eztags/tags/edit' )|wash(javascript)}' );" />
					<input type="hidden" name="DiscardConfirm" value="1" />
				</div>
			</div>
		</form>
	</div>
</div>

{include uri='design:ezjsctemplate/modal_dialog.tpl'}

{literal}
<script language="JavaScript" type="text/javascript">
<!--
function confirmDiscard( question )
{
    // Disable/bypass the reload-based (plain HTML) confirmation interface.
    document.tageditform.DiscardConfirm.value = "0";

    // Ask user if she really wants do it, return this to the handler.
    return confirm( question );
}
-->
</script>
{/literal}