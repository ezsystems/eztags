{def $tag = false()}

{if $attribute.content.tag_ids|count}
    <p>{'Tags'|i18n( 'extension/eztags/datatypes' )}:
    {foreach $attribute.content.tag_ids as $tag_id}
        {set $tag = fetch( tags, object, hash( tag_id, $tag_id ) )}
        <img class="transparent-png-icon" src={concat( 'tag_icons/small/', $tag.icon )|ezimage} title="{$tag.keyword|wash}" alt="{$tag.keyword|wash}" /> <a href={concat( '/tags/view/', $tag.url )|ezurl}>{$tag.keyword|wash}</a>{delimiter}, {/delimiter}
    {/foreach}</p>
{/if}

{undef $tag}