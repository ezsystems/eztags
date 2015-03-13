{foreach $tag_cloud as $tag_cloud_item}
    <a href={$tag_cloud_item.tag.url|ezurl} style="font-size: {$tag_cloud_item.font_size}%; padding-right:5px" title="{$tag_cloud_item.count} {"objects tagged with '%keyword'"|i18n( 'extension/eztags/tagcloud', '', hash( '%keyword', $tag_cloud_item.keyword|wash ) )}">{$tag_cloud_item.keyword|wash}</a>
{/foreach}
<<<<<<< HEAD

{undef $tag_object}
=======
>>>>>>> 06abc6e4d24cb0184dd64c8a211ac25dcafa5b1b
