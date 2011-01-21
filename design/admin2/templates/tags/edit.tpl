{ezcss_require(array('jqmodal.css', 'contentstructure-tree.css'))}
{ezscript_require(array('jqModal.js', 'eztagsselectparent.js'))}

<div class="context-block tags-edit">
	<div class="box-header">
		<h1 class="context-title">{"Edit tag"|i18n('extension/eztags/tags/edit')}: {$tag.keyword|wash(xhtml)} [{$tag.id}]</h1>
		<div class="header-mainline"></div>
	</div>

	<div class="box-content">
		<form name="tageditform" id="tageditform" enctype="multipart/form-data" method="post" action={concat('tags/edit/', $tag.id)|ezurl}>
			<div class="block tag-edit-keyword">
				<label>{'Tag name'|i18n( 'extension/eztags/tags/edit' )}</label>
				<input id="keyword" class="halfbox" type="text" size="70" name="TagEditKeyword" value="{$tag.keyword|wash(xhtml)}" />
			</div>

			{if $tag.lock_status|eq(0)}
				<div class="block tag-edit-parent">
					<label>{'Parent tag'|i18n( 'extension/eztags/tags/edit' )}</label>
					<input id="eztags_parent_id_0" type="hidden" name="TagEditParentID" value="{$tag.parent_id}" />
					<input id="hide_tag_id" type="hidden" name="TagHideID" value="{$tag.id}" />
					<span id="eztags_parent_keyword_0">{eztags_parent_string($tag.parent_id)|wash(xhtml)}</span>
					<input class="button" type="button" name="SelectParentButton" id="eztags-parent-selector-button-0" value="{'Select parent'|i18n( 'extension/eztags/tags/edit' )}" />
				</div>
			{/if}

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

{if $tag.lock_status|eq(0)}
	<div class="jqmDialog" id="parent-selector-tree">
		<div class="jqmdIn">
			<div class="jqmdTC"><span class="jqmdTCLeft"></span><span class="jqDrag">{'Select parent element in tag tree'|i18n( 'extension/eztags/tags/edit' )}</span><span class="jqmdTCRight"></span></div>
			<div class="jqmdBL"><div class="jqmdBR"><div class="jqmdBC"><div class="jqmdBCIn">
				{include uri='design:ezjsctemplate/menu.tpl'}
			</div></div></div></div>
			<a href="#" class="jqmdX jqmClose"></a>
		</div>
	</div>
{/if}

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