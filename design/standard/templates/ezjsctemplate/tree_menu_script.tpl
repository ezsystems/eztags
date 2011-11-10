{run-once}
{if is_unset( $current_user )}{def $current_user = fetch( user, current_user )}{/if}

<script type="text/javascript">
	var TagsStructureMenuModalParams = {ldelim}{*
		*}"perm":"{concat( $current_user.role_id_list|implode( ',' ), '|' , $current_user.limited_assignment_value_list|implode( ',' ) )|md5}",{*
		*}"expiry":"{fetch( content, content_tree_menu_expiry )}",{*
		*}"showTips":{if ezini( 'TreeMenu', 'ToolTips', 'eztags.ini' )|eq( 'enabled' )}true{else}false{/if},{*
		*}"tag_id_string":"{'Tag ID'|i18n( 'extension/eztags/tags/treemenu' )|wash( javascript )}",{*
		*}"parent_tag_id_string":"{'Parent tag ID'|i18n( 'extension/eztags/tags/treemenu' )|wash( javascript )}",{*
		*}"treemenu_base_url":"{'tags/treemenu'|ezurl( no )}",{*
		*}"not_allowed_string":"{'Dynamic tree not allowed for this siteaccess'|i18n( 'extension/eztags/tags/treemenu' )|wash( javascript )}",{*
		*}"no_tag_string":"{'Tag does not exist'|i18n( 'extension/eztags/tags/treemenu' )|wash( javascript )}",{*
		*}"internal_error_string":"{'Internal error'|i18n( 'extension/eztags/tags/treemenu' )|wash( javascript )}"{*
	*}{rdelim};
</script>
{/run-once}
