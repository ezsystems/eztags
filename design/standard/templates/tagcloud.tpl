{foreach $tag_cloud as $tag_cloud_item}
    <a href={$tag_cloud_item.tag.url|ezurl} style="font-size: {$tag_cloud_item.font_size}%; padding-right:5px" title="{$tag_cloud_item.count} {"objects tagged with '%keyword'"|i18n( 'extension/eztags/tagcloud', '', hash( '%keyword', $tag_cloud_item.keyword|wash ) )}">{$tag_cloud_item.keyword|wash}</a>
{/foreach}
