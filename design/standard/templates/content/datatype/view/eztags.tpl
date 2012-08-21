{if is_unset( $nice_urls )}{def $nice_urls = true()}{/if}

{if $attribute.has_content}
    {if is_set( $#persistent_variable.keywords )}
        {set scope='global' persistent_variable=$#persistent_variable|merge( hash( 'keywords', concat( $#persistent_variable.keywords, ', ', $attribute.content.meta_keyword_string ) ) )}
    {else}
        {if is_array( $#persistent_variable )|not()}
            {set scope='global' persistent_variable=hash( 'keywords', $attribute.content.meta_keyword_string )}
        {else}
            {set scope='global' persistent_variable=$#persistent_variable|merge( hash( 'keywords', $attribute.content.meta_keyword_string ) )}
        {/if}
    {/if}

    <p>{$attribute.contentclass_attribute.name|wash}:&nbsp;
    {foreach $attribute.content.tags as $tag}
        <img class="transparent-png-icon" src="{$tag.icon|tag_icon}" title="{$tag.keyword|wash}" alt="{$tag.keyword|wash}" />
        <a href={if $nice_urls}{$tag.url|ezurl}{else}{concat( 'tags/id/', $tag.id )|ezurl}{/if}>{$tag.keyword|wash}</a>
        {delimiter}, {/delimiter}
    {/foreach}</p>
{/if}
