{ezcss_require(array('jqmodal.css', 'contentstructure-tree.css'))}
{ezscript_require(array('jqModal.js', 'eztagsselectparent.js'))}

<div class="context-block tags-edit">
	<div class="box-header">
		<h1 class="context-title">{"Make synonym"|i18n('extension/eztags/tags/edit')}: {$tag.keyword|wash(xhtml)} [{$tag.id}]</h1>
		<div class="header-mainline"></div>
	</div>

	<div class="box-content">
		{if $tag.lock_status|eq(0)}
			<form name="tageditform" id="tageditform" enctype="multipart/form-data" method="post" action={concat('tags/makesynonym/', $tag.id)|ezurl}>
				<div class="block tag-edit-parent">
					<label>{'Main tag'|i18n( 'extension/eztags/tags/edit' )}</label>
					<input id="eztags_parent_id_0" type="hidden" name="MainTagID" value="0" />
					<input id="hide_tag_id_0" type="hidden" name="TagHideID" value="{$tag.id}" />
					<span id="eztags_parent_keyword_0">{eztags_parent_string(0)|wash(xhtml)}</span>
					<input class="button" type="button" name="SelectParentButton" id="eztags-parent-selector-button-0" value="{'Select main tag'|i18n( 'extension/eztags/tags/edit' )}" />
				</div>
	
				<div class="controlbar">
					<div class="block">
						<input class="defaultbutton" type="submit" name="SaveButton" value="{'Save'|i18n( 'extension/eztags/tags/edit' )}" />
						<input class="button" type="submit" name="DiscardButton" value="{'Discard'|i18n( 'extension/eztags/tags/edit' )}" onclick="return confirmDiscard( '{'Are you sure you want to discard changes?'|i18n( 'extension/eztags/tags/edit' )|wash(javascript)}' );" />
						<input type="hidden" name="DiscardConfirm" value="1" />
					</div>
				</div>
			</form>
		{else}
			{include uri='design:parts/tag_hard_locked.tpl' tag_id=$tag.id}
		{/if}
	</div>
</div>

{if $tag.lock_status|eq(0)}
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
{/if}