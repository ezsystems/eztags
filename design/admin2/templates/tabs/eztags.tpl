{def $eztags_attribute_count = 0}

{foreach $node.data_map as $attribute}
    {if $attribute.data_type_string|eq( 'eztags' )}
        <h4>{$attribute.contentclass_attribute.name} ({'Class attribute ID'|i18n( 'extension/eztags/node/view' )}: {$attribute.contentclass_attribute.id})</h4>

        {if $attribute.has_content}
            {attribute_view_gui attribute=$attribute nice_urls=false()}
        {else}
            <p>{'No tags'|i18n( 'extension/eztags/node/view' )}</p>
        {/if}

        {set $eztags_attribute_count = $eztags_attribute_count|inc}
    {/if}
{/foreach}

{if $eztags_attribute_count|eq( 0 )}
    <p>{'No tags'|i18n( 'extension/eztags/node/view' )}</p>
{/if}
