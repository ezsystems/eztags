{if is_unset( $nice_urls )}{def $nice_urls = true()}{/if}

{if $attribute.has_content}
    <p>{$attribute.contentclass_attribute.name|wash}:&nbsp;
    {foreach $attribute.content.tags as $tag}
        <img class="transparent-png-icon" src="{$tag.icon|tag_icon}" title="{$tag.keyword|wash}" alt="{$tag.keyword|wash}" />
        <a href={if $nice_urls}{$tag.url|ezurl}{else}{concat( 'tags/id/', $tag.id )|ezurl}{/if}>{$tag.keyword|wash}</a>
        {delimiter}, {/delimiter}
    {/foreach}</p>
{/if}
