{foreach $node.data_map as $identifier => $attribute}
	{if $attribute.data_type_string|eq( 'eztags' )}
		<h3>{$attribute.contentclass_attribute.name} (Class ID: {$attribute.contentclass_attribute.id})</h3>

		{attribute_view_gui view=$attribute}
	{/if}
{/foreach}
