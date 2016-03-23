{if $permission_array.can_add}
    {include uri='design:ezjsctemplate/modal_dialog.tpl' attribute_id=$attribute.id root_tag=$permission_array.allowed_locations_tags}
{/if}

{if $attribute.contentclass_attribute.data_text1|eq( 'Tree' )}
    <div class="block">
        <div class="ez-tags-tree-selector"
            data-config-url="{concat('/ezjscore/call/ezjsctags::treeConfig::', $attribute.id, '::', $attribute.version)|ezurl(no)}"
            data-base-url="{'/ezjscore/call/ezjsctags::tree::'|ezurl(no)}">
        </div>
    </div>
{/if}
