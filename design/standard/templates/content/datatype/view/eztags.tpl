{if $attribute.content.tag_ids|count}
{if is_set( $#persistent_variable.keywords )}
    {set scope='global' persistent_variable=$#persistent_variable|merge( hash( 'keywords', concat( $#persistent_variable.keywords, ', ', $attribute.content.meta_keyword_string ) ) )}
{else}
    {if is_array( $#persistent_variable )|not()}
        {set scope='global' persistent_variable=hash( 'keywords', $attribute.content.meta_keyword_string )}
    {else}
        {set scope='global' persistent_variable=$#persistent_variable|merge( hash( 'keywords', $attribute.content.meta_keyword_string ) )}
    {/if}
{/if}
    <p>{'Tags'|i18n( 'extension/eztags/datatypes' )}:
    {foreach $attribute.content.tags as $tag}
        <img class="transparent-png-icon" src={concat( 'tag_icons/small/', $tag.icon )|ezimage} title="{$tag.keyword|wash}" alt="{$tag.keyword|wash}" /> <a href={concat( '/tags/view/', $tag.url )|ezurl}>{$tag.keyword|wash}</a>{delimiter}, {/delimiter}
    {/foreach}</p>
{/if}