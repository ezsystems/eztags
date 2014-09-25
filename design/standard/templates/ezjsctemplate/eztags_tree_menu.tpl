{run-once}

{ezcss_require('jstree/style.min.css')}
{ezscript_require(array('jstree.min.js'))}

<script type="text/javascript">

window.eztags_treemenu_url = '{'/tags/treemenu'|ezurl(no)}';

</script>
{/run-once}
{def $tag = fetch( tags, tag, hash( 'tag_id', $attribute.contentclass_attribute.data_int1 ) )}
<script type="text/javascript">
function getTagTree_{$attribute_id}(node, cb) {ldelim}
    {literal}
    var tagID = node.id;
    if(tagID == "#") {
        {/literal}
        tagID = {if $attribute.contentclass_attribute.data_int1|eq(0)}0{else}{$attribute.contentclass_attribute.data_int1}{/if};
        {literal}
    }

    $.getJSON( window.eztags_treemenu_url + '/' + tagID, function( d ) {
        var tagTree = [];
        {/literal}
        var parentID = {if $attribute.contentclass_attribute.data_int1}{$attribute.contentclass_attribute.data_int1}{else}0{/if};
        var hideRoot = {if $attribute.contentclass_attribute.data_int3}1{else}0{/if};

        if( window.eztag_tree_started_{$attribute_id} || hideRoot == 1 )
        {literal}
        {
            for( var child in d.children ) {
                var tagTreeItem = {
                 'text' : d.children[child].keyword,
                 'icon' : d.children[child].icon,
                 'id' : d.children[child].id,
                 'children' : d.children[child].has_children == 1 ? true : false,
                 {/literal}
                 'parent' : (d.children[child].parent_id == parentID && hideRoot == 1 ) ? '#' : d.children[child].parent_id,
                 {literal}
                 'state' : { 'opened' : false, 'selected' : false }
                };

                tagTree.push(tagTreeItem);
            }
        }
        else
        {
        {/literal}
            var parentData = {ldelim}
             'text' : {if $tag}'{$tag.keyword|wash(javascript)}'{else}"{"Top Level Tags"|i18n('extension/eztags/tags/treemenu')|wash(javascript)}"{/if},
             'icon' : {if $tag}{concat('tag_icons/small/', $tag.icon)|ezimage}{else}{concat('tag_icons/small/', ezini('Icons', 'Default', 'eztags.ini'))|ezimage}{/if},
             'id' : {if $tag}{$tag.id}{else}0{/if},
             'children' : {if or( $tag|not, $tag.children_count|gt(0) )}true{else}false{/if},
             'parent' : '#',
             'state' : {ldelim} 'opened' : false, 'selected' : false {rdelim}
            {rdelim};
            tagTree.push(parentData);
            window.eztag_tree_started_{$attribute_id} = true;
        {literal}
        };
        cb(tagTree);
    {/literal}
    });
{rdelim}
window.eztag_tree_started_{$attribute_id} = false;
window.eztag_tree_max_{$attribute_id} = {if $attribute.contentclass_attribute.data_int4|gt(0)}{$attribute.contentclass_attribute.data_int4}{else}0{/if};

$(function () {ldelim}
    $('#tag-tree-selector-{$attribute_id}')
    {literal}
    .on('changed.jstree', function (e, data) {
        var i, j, r = [];
        {/literal}
                console.log(data.node.id );
          if( ( window.eztag_tree_max_{$attribute_id} == 0 || window.eztags_map[{$attribute_id}].length() < window.eztag_tree_max_{$attribute_id} ) && data.node.id != 0 )
          {ldelim}
          var item = {ldelim} 'tag_name': data.node.text, 'tag_parent_id': data.node.parent, 'tag_id': data.node.id {rdelim};
          window.eztags_map[{$attribute_id}].addTagToList(item);
          {rdelim}
        {literal}
      })
    .jstree(
        {
           'core' :
           {
               'multiple' : false,
               {/literal}
               'data' : getTagTree_{$attribute_id}
               {literal}
           }
        }
    );
});
{/literal}
</script>

<div id="tag-tree-selector-{$attribute_id}" class="eztag_tree_menu"></div>