{def $tags_add_access = fetch( user, has_access_to, hash( module, tags, function, add ) )
     $tags_edit_access = fetch( user, has_access_to, hash( module, tags, function, edit ) )
     $tags_delete_access = fetch( user, has_access_to, hash( module, tags, function, delete ) )
     $tags_merge_access = fetch( user, has_access_to, hash( module, tags, function, merge ) )
     $tags_add_synonym_access = fetch( user, has_access_to, hash( module, tags, function, addsynonym ) )
     $tags_make_synonym_access = fetch( user, has_access_to, hash( module, tags, function, makesynonym ) )
     $show_full_menu = or( $tags_add_access, $tags_edit_access, $tags_delete_access, $tags_merge_access, $tags_add_synonym_access, $tags_make_synonym_access )}

{if $show_full_menu}
    <script type="text/javascript">
        menuArray['TagMenu'] = {ldelim} 'depth': 0, 'headerID': 'tag-header' {rdelim};
        menuArray['TagMenu']['elements'] = {ldelim}{rdelim};

        {if $tags_add_access}
            menuArray['TagMenu']['elements']['add-child-tag'] = {ldelim} 'url': {"/tags/add/%tagID%"|ezurl} {rdelim};
        {/if}

        {if $tags_edit_access}
            menuArray['TagEditSubmenu'] = {ldelim} 'depth': 1 {rdelim};
            menuArray['TagEditSubmenu']['elements'] = {ldelim}{rdelim};
            menuArray['TagEditSubmenu']['elements']['edit-tag-languages'] = {ldelim} 'variable': '%languages%' {rdelim};
            menuArray['TagEditSubmenu']['elements']['edit-tag-languages']['content'] = '<a href={"/tags/edit/%tagID%/%locale%"|ezurl} onmouseover="ezpopmenu_mouseOver( \'TagEditSubmenu\' )">%name%<\/a>';
            menuArray['TagEditSubmenu']['elements']['edit-tag-languages-new'] = {ldelim} 'url': {"/tags/edit/%tagID%"|ezurl} {rdelim};
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
    </script>
{/if}

{if $tags_add_access}
    <script type="text/javascript">
        menuArray['TagMenuSimple'] = {ldelim} 'depth': 0, 'headerID': 'tag-simple-header' {rdelim};
        menuArray['TagMenuSimple']['elements'] = {ldelim}{rdelim};
        menuArray['TagMenuSimple']['elements']['add-child-tag-simple'] = {ldelim} 'url': {"/tags/add/%tagID%"|ezurl} {rdelim};
    </script>
{/if}

{if $show_full_menu}
    <div class="popupmenu" id="TagMenu">
        <div class="popupmenuheader"><h3 id="tag-header">XXX</h3>
            <div class="break"></div>
        </div>
        {if $tags_add_access}
            <a id="add-child-tag" href="#" onmouseover="ezpopmenu_mouseOver( 'TagMenu' )">{"Add child tag"|i18n( "extension/eztags/tags/treemenu" )}</a>
        {/if}
        {if $tags_edit_access}
            <a id="edit-tag" href="#" class="more" onmouseover="ezpopmenu_showSubLevel( event, 'TagEditSubmenu', 'edit-tag' ); return false;">{"Edit tag"|i18n( "extension/eztags/tags/treemenu" )}</a>
        {/if}
        {if $tags_delete_access}
            <a id="delete-tag" href="#" onmouseover="ezpopmenu_mouseOver( 'TagMenu' )">{"Delete tag"|i18n( "extension/eztags/tags/treemenu" )}</a>
        {/if}
        {if $tags_merge_access}
            <a id="merge-tag" href="#" onmouseover="ezpopmenu_mouseOver( 'TagMenu' )">{"Merge tag"|i18n( "extension/eztags/tags/treemenu" )}</a>
        {/if}
        <hr />
        {if $tags_add_synonym_access}
            <a id="add-synonym-tag" href="#" onmouseover="ezpopmenu_mouseOver( 'TagMenu' )">{"Add synonym"|i18n( "extension/eztags/tags/treemenu" )}</a>
        {/if}
        {if $tags_make_synonym_access}
            <a id="make-synonym-tag" href="#" onmouseover="ezpopmenu_mouseOver( 'TagMenu' )">{"Convert to synonym"|i18n( "extension/eztags/tags/treemenu" )}</a>
        {/if}
    </div>
{/if}

{if $tags_edit_access}
    <div class="popupmenu" id="TagEditSubmenu">
        <div id="edit-tag-languages"></div>
        <hr />
        <a id="edit-tag-languages-new" href="#" onmouseover="ezpopmenu_mouseOver( 'TagEditSubmenu' )">{'New translation'|i18n( 'extension/eztags/tags/treemenu' )}</a>
    </div>
{/if}

{if $tags_add_access}
    <div class="popupmenu" id="TagMenuSimple">
        <div class="popupmenuheader"><h3 id="tag-simple-header">XXX</h3>
            <div class="break"></div>
        </div>
        <a id="add-child-tag-simple" href="#" onmouseover="ezpopmenu_mouseOver( 'TagMenuSimple' )">{"Add child tag"|i18n( "extension/eztags/tags/treemenu" )}</a>
    </div>
{/if}

{undef}
