{foreach $node.data_map as $identifier => $attribute}
	{if $attribute.data_type_string|eq( 'eztags' )}
		<h4>{$attribute.contentclass_attribute.name} ({'Class attribute ID'|i18n( 'extension/eztags/node/view' )}: {$attribute.contentclass_attribute.id})</h4>

		{attribute_view_gui attribute=$attribute nice_urls=false()}
	{/if}
{/foreach}
