{def $permission_array = $attribute.content.permission_array}

{default attribute_base=ContentObjectAttribute}
<div id="eztags{$attribute.id}" class="tagssuggest{if $attribute.contentclass_attribute.data_int2} tagsfilter{/if}">
    <input id="ezcoa-{if ne( $attribute_base, 'ContentObjectAttribute' )}{$attribute_base}-{/if}{$attribute.contentclassattribute_id}_{$attribute.contentclass_attribute_identifier}" class="tagnames" type="hidden" name="{$attribute_base}_eztags_data_text_{$attribute.id}" value="{$attribute.content.keyword_string|wash}"  />
    <input id="ezcoa2-{if ne( $attribute_base, 'ContentObjectAttribute' )}{$attribute_base}-{/if}{$attribute.contentclassattribute_id}_{$attribute.contentclass_attribute_identifier}" class="tagpids" type="hidden" name="{$attribute_base}_eztags_data_text2_{$attribute.id}" value="{$attribute.content.parent_string|wash}"  />
    <input id="ezcoa3-{if ne( $attribute_base, 'ContentObjectAttribute' )}{$attribute_base}-{/if}{$attribute.contentclassattribute_id}_{$attribute.contentclass_attribute_identifier}" class="tagids" type="hidden" name="{$attribute_base}_eztags_data_text3_{$attribute.id}" value="{$attribute.content.id_string|wash}"  />
    <input id="ezcoa4-{if ne( $attribute_base, 'ContentObjectAttribute' )}{$attribute_base}-{/if}{$attribute.contentclassattribute_id}_{$attribute.contentclass_attribute_identifier}" class="taglocales" type="hidden" name="{$attribute_base}_eztags_data_text4_{$attribute.id}" value="{$attribute.content.locale_string|wash}"  />
</div>

{if $permission_array.can_add}
    {include uri='design:ezjsctemplate/modal_dialog.tpl' attribute_id=$attribute.id root_tag=$permission_array.allowed_locations_tags}
{/if}

<script type="text/javascript">
{run-once}
var eZTagsTranslations = {ldelim}{*
    *}"selectedTags":"{'Selected tags'|i18n( 'extension/eztags/datatypes' )}",{*
    *}"loading":"{'Loading'|i18n( 'extension/eztags/datatypes' )}...",{*
    *}"noSelectedTags":"{'There are no selected tags'|i18n( 'extension/eztags/datatypes' )}",{*
    *}"suggestedTags":"{'Suggested tags'|i18n( 'extension/eztags/datatypes' )}",{*
    *}"noSuggestedTags":"{'There are no tags to suggest'|i18n( 'extension/eztags/datatypes' )}",{*
    *}"addNew":"{'Add new'|i18n( 'extension/eztags/datatypes' )}",{*
    *}"clickAddThisTag":"{'Click to add this tag'|i18n( 'extension/eztags/datatypes' )}",{*
    *}"removeTag":"{'Remove tag'|i18n( 'extension/eztags/datatypes' )}",{*
    *}"translateTag":"{'Translate tag'|i18n( 'extension/eztags/datatypes' )}",{*
    *}"existingTranslations":"{'Existing translations'|i18n( 'extension/eztags/datatypes' )}",{*
    *}"noExistingTranslations":"{'No existing translations'|i18n( 'extension/eztags/datatypes' )}",{*
    *}"addTranslation":"{'Add translation'|i18n( 'extension/eztags/datatypes' )}",{*
    *}"cancel":"{'Cancel'|i18n( 'extension/eztags/datatypes' )}",{*
    *}"ok":"{'OK'|i18n( 'extension/eztags/datatypes' )}",{*
*}{rdelim};
{/run-once}
jQuery('#eztags{$attribute.id}').eZTags({ldelim}{*
    *}"ajaxResults":true,{*
    *}"maxResults":24,{*
    *}"minCharacters":1,{*
    *}"hasAddAccess":{cond( $permission_array.can_add, 'true', true(), 'false' )},{*
    *}"subtreeLimit":{$attribute.contentclass_attribute.data_int1},{*
    *}"hideRootTag":{$attribute.contentclass_attribute.data_int3},{*
    *}"maxTags":{if $attribute.contentclass_attribute.data_int4|gt( 0 )}{$attribute.contentclass_attribute.data_int4}{else}0{/if},{*
    *}"locale":"{$attribute.language_code}",{*
    *}"translations":eZTagsTranslations,{*
    *}"iconPath":"{'eng-GB'|flag_icon()|explode('src="')|extract_right(1)|implode('')|explode('eng-GB')|extract_left(1)|implode('')}"{*
*}{rdelim});
</script>
{/default}
