{def $eztags_attribute_count = 0}

{foreach $node.data_map as $attribute}
    {if $attribute.data_type_string|eq( 'eztags' )}
        {if $attribute.has_content}
            {attribute_view_gui attribute=$attribute nice_urls=false()}
            {set $eztags_attribute_count = $eztags_attribute_count|inc}
        {/if}
    {/if}
{/foreach}

{if $eztags_attribute_count|eq( 0 )}
    <p>{'No tags'|i18n( 'extension/eztags/node/view' )}</p>
{/if}
