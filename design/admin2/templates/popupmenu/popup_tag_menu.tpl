{def $tags_add_access = fetch( user, has_access_to, hash( module, tags, function, add ) )
     $tags_edit_access = fetch( user, has_access_to, hash( module, tags, function, edit ) )
     $tags_delete_access = fetch( user, has_access_to, hash( module, tags, function, delete ) )
     $tags_merge_access = fetch( user, has_access_to, hash( module, tags, function, merge ) )
     $tags_add_synonym_access = fetch( user, has_access_to, hash( module, tags, function, addsynonym ) )
     $tags_make_synonym_access = fetch( user, has_access_to, hash( module, tags, function, makesynonym ) )}

<script type="text/javascript">
menuArray['TagMenu'] = {ldelim} 'depth': 0, 'headerID': 'tag-header' {rdelim};
menuArray['TagMenu']['elements'] = {ldelim}{rdelim};
{if $tags_add_access}
    menuArray['TagMenu']['elements']['add-child-tag'] = {ldelim} 'url': {"/tags/add/%tagID%"|ezurl} {rdelim};
{/if}
{if $tags_edit_access}
    menuArray['TagMenu']['elements']['edit-tag'] = {ldelim} 'url': {"/tags/edit/%tagID%"|ezurl} {rdelim};
{/if}
{if $tags_delete_access}
    menuArray['TagMenu']['elements']['delete-tag'] = {ldelim} 'url': {"/tags/delete/%tagID%"|ezurl} {rdelim};
{/if}
{if $tags_merge_access}
    menuArray['TagMenu']['elements']['merge-tag'] = {ldelim} 'url': {"/tags/merge/%tagID%"|ezurl} {rdelim};
{/if}
{if $tags_add_synonym_access}
    menuArray['TagMenu']['elements']['add-synonym-tag'] = {ldelim} 'url': {"/tags/addsynonym/%tagID%"|ezurl} {rdelim};
{/if}
{if $tags_make_synonym_access}
    menuArray['TagMenu']['elements']['make-synonym-tag'] = {ldelim} 'url': {"/tags/makesynonym/%tagID%"|ezurl} {rdelim};
{/if}

{if $tags_add_access}
    menuArray['TagMenuSimple'] = {ldelim} 'depth': 0, 'headerID': 'tag-simple-header' {rdelim};
    menuArray['TagMenuSimple']['elements'] = {ldelim}{rdelim};
    menuArray['TagMenuSimple']['elements']['add-child-tag-simple'] = {ldelim} 'url': {"/tags/add/%tagID%"|ezurl} {rdelim};
{/if}
</script>
<div class="popupmenu" id="TagMenu">
    <div class="popupmenuheader"><h3 id="tag-header">XXX</h3>
        <div class="break"></div>
    </div>
    {if $tags_add_access}
        <a id="add-child-tag" href="#" onmouseover="ezpopmenu_mouseOver( 'TagMenu' )">{"Add child tag"|i18n( "extension/eztags/tags/edit" )}</a>
    {/if}
    {if $tags_edit_access}
        <a id="edit-tag" href="#" onmouseover="ezpopmenu_mouseOver( 'TagMenu' )">{"Edit tag"|i18n( "extension/eztags/tags/edit" )}</a>
    {/if}
    {if $tags_delete_access}
        <a id="delete-tag" href="#" onmouseover="ezpopmenu_mouseOver( 'TagMenu' )">{"Delete tag"|i18n( "extension/eztags/tags/edit" )}</a>
    {/if}
    {if $tags_merge_access}
        <a id="merge-tag" href="#" onmouseover="ezpopmenu_mouseOver( 'TagMenu' )">{"Merge tag"|i18n( "extension/eztags/tags/edit" )}</a>
    {/if}
    <hr />
    {if $tags_add_synonym_access}
        <a id="add-synonym-tag" href="#" onmouseover="ezpopmenu_mouseOver( 'TagMenu' )">{"Add synonym"|i18n( "extension/eztags/tags/edit" )}</a>
    {/if}
    {if $tags_make_synonym_access}
        <a id="make-synonym-tag" href="#" onmouseover="ezpopmenu_mouseOver( 'TagMenu' )">{"Convert to synonym"|i18n( "extension/eztags/tags/edit" )}</a>
    {/if}
</div>

{if $tags_add_access}
    <div class="popupmenu" id="TagMenuSimple">
        <div class="popupmenuheader"><h3 id="tag-simple-header">XXX</h3>
            <div class="break"></div>
        </div>
        <a id="add-child-tag-simple" href="#" onmouseover="ezpopmenu_mouseOver( 'TagMenuSimple' )">{"Add child tag"|i18n( "extension/eztags/tags/edit" )}</a>
    </div>
{/if}

{undef}