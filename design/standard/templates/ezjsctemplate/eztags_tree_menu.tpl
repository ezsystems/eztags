{run-once}

{ezcss_require('jstree/style.min.css')}
{ezscript_require(array('jstree.min.js', 'ezjsc::jqueryUI'))}

<script type="text/javascript">

window.eztags_treemenu_url = '{'/tags/treemenu'|ezurl(no)}';

</script>
{/run-once}

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
        for( var child in d.children ) {
            var tagTreeItem = {
             'text' : d.children[child].keyword,
             'icon' : d.children[child].icon,
             'id' : d.children[child].id,
             'children' : d.children[child].has_children == 1 ? true : false,
             {/literal}
             'parent' : d.children[child].parent_id == {$attribute.contentclass_attribute.data_int1} ? '#' : d.children[child].parent_id,
             {literal}
             'state' : { 'opened' : false, 'selected' : false }
            };

            tagTree.push(tagTreeItem);
        }
        cb(tagTree);

    } );
    {/literal}
{rdelim}

$(function () {ldelim}
            $('#tagssuggest_{$attribute_id} ul').sortable({ldelim}
                    stop: function(event, ui){ldelim}
                        var data = [];
                        window.eztags_map[{$attribute_id}].obj.find('div.tags-listed ul li').each(function(){ldelim}
                            data.push( $(this).data('tag' ) );
                            window.eztags_map[{$attribute_id}].removeTagFromList($(this));
                        {rdelim});
                        for(var x in data)
                        {ldelim}
                            window.eztags_map[{$attribute_id}].addTagToList(data[x]);
                        {rdelim}
                    {rdelim}
             {rdelim});
			$('#tagssuggest_{$attribute_id} ul').disableSelection();
            $('#tag-tree-selector-{$attribute_id}')
            {literal}
            .on('changed.jstree', function (e, data) {
                var i, j, r = [];
                {/literal}
                  var item = {ldelim} 'tag_name': data.node.text, 'tag_parent_id': data.node.parent, 'tag_id': data.node.id {rdelim};
                  window.eztags_map[{$attribute_id}].addTagToList(item);
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

<div class="jstree_demo_div" id="tag-tree-selector-{$attribute_id}"></div>
