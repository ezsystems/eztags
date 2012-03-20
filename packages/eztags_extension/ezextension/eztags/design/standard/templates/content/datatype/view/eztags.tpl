{if $attribute.content.tag_ids|count}
    <p>{'Tags'|i18n( 'extension/eztags/datatypes' )}:
    {foreach $attribute.content.tags as $tag}
        <img class="transparent-png-icon" src={concat( 'tag_icons/small/', $tag.icon )|ezimage} title="{$tag.keyword|wash}" alt="{$tag.keyword|wash}" /> <a href={concat( '/tags/view/', $tag.url )|ezurl}>{$tag.keyword|wash}</a>{delimiter}, {/delimiter}
    {/foreach}</p>
{/if}