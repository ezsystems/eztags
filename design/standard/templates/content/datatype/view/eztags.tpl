{if is_unset( $nice_urls )}{def $nice_urls = true()}{/if}

{if $attribute.content.tag_ids|count}
    <p>{'Tags'|i18n( 'extension/eztags/datatypes' )}:
    {foreach $attribute.content.tags as $tag}
        <img class="transparent-png-icon" src={concat( 'tag_icons/small/', $tag.icon )|ezimage} title="{$tag.keyword|wash}" alt="{$tag.keyword|wash}" />
        <a href={if $nice_urls}{$tag.url|ezurl}{else}{concat( 'tags/id/', $tag.id )|ezurl}{/if}>{$tag.keyword|wash}</a>
        {delimiter}, {/delimiter}
    {/foreach}</p>
{/if}
