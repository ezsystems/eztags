<script type="text/javascript">
if (typeof treeMenu_{$attribute_id} == 'undefined') {ldelim}
var treeMenu_{$attribute_id};
(function(){ldelim}
    var root_tag_id             = {if $root_tag}{$root_tag.id}{else}0{/if};
    var currentDate             = new Date().valueOf();
    treeMenu_{$attribute_id}    = new TagsStructureMenu( TagsStructureMenuParams, '{$attribute_id}' );

    {if $root_tag}
        var rootTag = {ldelim}{*
            *}"id":{$root_tag.id},{*
            *}"parent_id":{$root_tag.parent_id},{*
            *}"has_children":{if $root_tag.children_count|gt(0)}true{else}false{/if},{*
            *}"synonyms_count":{$root_tag.synonyms_count},{*
            *}"keyword":"{$root_tag.keyword|wash(javascript)}",{*
            *}"url":{concat('tags/id/', $root_tag.id)|ezurl},{*
            *}"icon":"{$root_tag.icon|tag_icon}",{*
            *}"modified":{$root_tag.modified}{rdelim};
    {else}
        var rootTag = {ldelim}{*
            *}"id":0,{*
            *}"parent_id":0,{*
            *}"has_children":true,{*
            *}"keyword":"{"Top level tags"|i18n('extension/eztags/tags/treemenu')|wash(javascript)}",{*
            *}"url":{'tags/dashboard'|ezurl},{*
            *}"icon":"{ezini( 'Icons', 'Default', 'eztags.ini' )|tag_icon}",{*
            *}"modified":currentDate{rdelim};
    {/if}

    document.writeln( '<ul class="content_tree_menu">' );
    document.writeln( treeMenu_{$attribute_id}.generateEntry( rootTag, false, true ) );
    document.writeln( '<\/ul>' );

    {if $root_tag}
        treeMenu_{$attribute_id}.load( true, {$root_tag.id}, {$root_tag.modified} );
    {else}
        treeMenu_{$attribute_id}.load( false, 0, currentDate );
    {/if}
{rdelim})();
{rdelim}
</script>
