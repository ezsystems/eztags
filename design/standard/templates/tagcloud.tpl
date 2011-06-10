{def $tag_object = false()}

{foreach $tag_cloud as $tag}
    {set $tag_object = fetch( tags, tag, hash( tag_id, $tag['id'] ) )}
    <a href={concat( 'tags/view/', $tag_object.url )|ezurl} style="font-size: {$tag['font_size']}%" title="{$tag['count']} objects tagged with '{$tag_object.keyword|wash}'">{$tag_object.keyword|wash}</a>
{/foreach}

{undef $tag_object}