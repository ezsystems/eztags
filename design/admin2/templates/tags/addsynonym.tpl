{ezcss_require(array('jqmodal.css', 'contentstructure-tree.css'))}
{ezscript_require(array('jqModal.js', 'eztagsselectparent.js'))}

<div class="context-block tags-add">
	<div class="box-header">
		<h1 class="context-title">{"New synonym tag"|i18n('extension/eztags/tags/edit')} ({"Main tag ID"|i18n('extension/eztags/tags/edit')}: {$main_tag.id}, {"Main tag name"|i18n('extension/eztags/tags/edit')}: {$main_tag.keyword|wash})</h1>
		<div class="header-mainline"></div>
	</div>

	<div class="box-content">
		<form name="tageditform" id="tageditform" enctype="multipart/form-data" method="post" action={concat('tags/addsynonym/', $main_tag.id)|ezurl}>
			<div class="block tag-edit-keyword">
				<label>{'Synonym name'|i18n( 'extension/eztags/tags/edit' )}</label>
				<input id="keyword" class="halfbox" type="text" size="70" name="TagEditKeyword" value="" />
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

<div class="jqmDialog" id="parent-selector-tree">
	<div class="jqmdIn">
		<div class="jqmdTC"><span class="jqmdTCLeft"></span><span class="jqDrag">{'Select parent element in tag tree'|i18n( 'extension/eztags/tags/edit' )}</span><span class="jqmdTCRight"></span></div>
		<div class="jqmdBL"><div class="jqmdBR"><div class="jqmdBC"><div class="jqmdBCIn">
			{include uri='design:ezjsctemplate/menu.tpl'}
		</div></div></div></div>
		<a href="#" class="jqmdX jqmClose"></a>
	</div>
</div>

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