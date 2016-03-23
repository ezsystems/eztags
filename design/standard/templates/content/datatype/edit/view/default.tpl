{if $permission_array.can_add}
    {include uri='design:ezjsctemplate/modal_dialog.tpl' attribute_id=$attribute.id root_tag=$permission_array.allowed_locations_tags}
{/if}
