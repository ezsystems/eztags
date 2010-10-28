<script type="text/javascript">
<!--
menuArray['TagMenu'] = {ldelim} 'depth': 0, 'headerID': 'tag-header' {rdelim};
menuArray['TagMenu']['elements'] = {ldelim}{rdelim};
menuArray['TagMenu']['elements']['add-child-tag'] = {ldelim} 'url': {"/tags/add/%tagID%"|ezurl} {rdelim};
menuArray['TagMenu']['elements']['edit-tag'] = {ldelim} 'url': {"/tags/edit/%tagID%"|ezurl} {rdelim};
menuArray['TagMenu']['elements']['delete-tag'] = {ldelim} 'url': {"/tags/delete/%tagID%"|ezurl} {rdelim};

menuArray['TagMenuSimple'] = {ldelim} 'depth': 0, 'headerID': 'tag-simple-header' {rdelim};
menuArray['TagMenuSimple']['elements'] = {ldelim}{rdelim};
menuArray['TagMenuSimple']['elements']['add-child-tag-simple'] = {ldelim} 'url': {"/tags/add/%tagID%"|ezurl} {rdelim};
// -->
</script>
<div class="popupmenu" id="TagMenu">
    <div class="popupmenuheader"><h3 id="tag-header">XXX</h3>
        <div class="break"></div>
    </div>
    <a id="add-child-tag" href="#" onmouseover="ezpopmenu_mouseOver( 'TagMenu' )">{"Add child tag"|i18n("extension/eztags/tags/edit")}</a>
    <a id="edit-tag" href="#" onmouseover="ezpopmenu_mouseOver( 'TagMenu' )">{"Edit tag"|i18n("extension/eztags/tags/edit")}</a>
    <a id="delete-tag" href="#" onmouseover="ezpopmenu_mouseOver( 'TagMenu' )">{"Delete tag"|i18n("extension/eztags/tags/edit")}</a>
</div>

<div class="popupmenu" id="TagMenuSimple">
    <div class="popupmenuheader"><h3 id="tag-simple-header">XXX</h3>
        <div class="break"></div>
    </div>
    <a id="add-child-tag-simple" href="#" onmouseover="ezpopmenu_mouseOver( 'TagMenuSimple' )">{"Add child tag"|i18n("extension/eztags/tags/edit")}</a>
</div>