{ezcss_require(array('tagssuggest.css', 'jqmodal.css', 'contentstructure-tree.css'))}
{ezscript_require(array('jqModal.js', 'jquery.tagsSuggest-dev.js', 'tagsSuggest-init.js'))}

{default attribute_base=ContentObjectAttribute}
<fieldset>
	<legend>Tags</legend>

	<div class="tagssuggest" id="tagssuggest">
		<label>Selected tags:</label>
		<div class="tags-list tags-listed no-results">
			<p class="loading">Loading...</p>
			<p class="no-results">There are no selected tags.</p>
		</div>

		<label>Suggested tags:</label>
		<div class="tags-list tags-suggested no-results">
			<p class="loading">Loading...</p>
			<p class="no-results">There are no tags to suggest.</p>
		</div>

		<div style="float:left; position:relative; width:300px; margin:0 12px 0 0;">
			<input class="tagssuggestfield" style="width:296px;" type="text" size="70" name="xxx_{$attribute_base}_eztags_data_text_{$attribute.id}" value="" autocomplete="off"  />
		</div>
		<input type="button" value="Add new" name="AddTagButton" class="button-disabled" disabled="disabled">

		<input id="ezcoa-{if ne( $attribute_base, 'ContentObjectAttribute' )}{$attribute_base}-{/if}{$attribute.contentclassattribute_id}_{$attribute.contentclass_attribute_identifier}" class="box ezcc-{$attribute.object.content_class.identifier} ezcca-{$attribute.object.content_class.identifier}_{$attribute.contentclass_attribute_identifier} tagnames" type="hidden" name="{$attribute_base}_eztags_data_text_{$attribute.id}" value="{$attribute.content.keyword_string|wash(xhtml)}"  />

		<input id="ezcoa2-{if ne( $attribute_base, 'ContentObjectAttribute' )}{$attribute_base}-{/if}{$attribute.contentclassattribute_id}_{$attribute.contentclass_attribute_identifier}" class="box tagpids" type="hidden" name="{$attribute_base}_eztags_data_text2_{$attribute.id}" value="{$attribute.content.parent_string|wash(xhtml)}"  />
	</div>

	<div class="jqmDialog parent-selector-tree">
		<div class="jqmdIn">
			<div class="jqmdTC"><span class="jqmdTCLeft"></span><span class="jqDrag">{'Adding new tag - Select parent element in tag tree'|i18n( 'design/admin/parts/tags/menu' )}</span><span class="jqmdTCRight"></span></div>
			<div class="jqmdBL"><div class="jqmdBR"><div class="jqmdBC"><div class="jqmdBCIn">
				{include uri='design:ezjsctemplate/menu.tpl'}
			</div></div></div></div>
			<a href="#" class="jqmdX jqmClose"></a>
		</div>
	</div>
</fieldset>
{/default}