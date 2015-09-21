{if is_unset( $menu_persistence )}{def $menu_persistence = ezini( 'TreeMenu', 'MenuPersistence', 'eztags.ini' )|eq( 'enabled' )}{/if}
{if is_unset( $current_user )}{def $current_user = fetch( user, current_user )}{/if}

<script type="text/javascript">
var treeMenu_0;

var TagsStructureMenuParams = {ldelim}{*
    *}"path":[{if is_set( $module_result.path[0].tag_id )}{foreach $module_result.path as $element}'{$element.tag_id}'{delimiter}, {/delimiter}{/foreach}{/if}],{*
    *}"useCookie":{if $menu_persistence}true{else}false{/if},{*
    *}"perm":"{concat( $current_user.role_id_list|implode( ',' ), '|' , $current_user.limited_assignment_value_list|implode( ',' ) )|md5}",{*
    *}"expiry":"{fetch( content, content_tree_menu_expiry )}",{*
    *}"modal":false,{*
    *}"context":"{$ui_context}",{*
    *}"showTips":{if ezini( 'TreeMenu', 'ToolTips', 'eztags.ini' )|eq( 'enabled' )}true{else}false{/if},{*
    *}"autoOpen":{if ezini( 'TreeMenu', 'AutoopenCurrentTag', 'eztags.ini' )|eq( 'enabled' )}true{else}false{/if},{*
    *}"tag_id_string":"{'Tag ID'|i18n( 'extension/eztags/tags/treemenu' )|wash( javascript )}",{*
    *}"parent_tag_id_string":"{'Parent tag ID'|i18n( 'extension/eztags/tags/treemenu' )|wash( javascript )}",{*
    *}"treemenu_base_url":"{'tags/treemenu'|ezurl( no )}",{*
    *}"not_allowed_string":"{'Dynamic tree not allowed for this siteaccess'|i18n( 'extension/eztags/tags/treemenu' )|wash( javascript )}",{*
    *}"no_tag_string":"{'Tag does not exist'|i18n( 'extension/eztags/tags/treemenu' )|wash( javascript )}",{*
    *}"internal_error_string":"{'Internal error'|i18n( 'extension/eztags/tags/treemenu' )|wash( javascript )}"{*
*}{rdelim};

(function(){ldelim}
    var currentDate  = new Date().valueOf();
    treeMenu_0       = new TagsStructureMenu( TagsStructureMenuParams, '0' );

    var rootTag = {ldelim}{*
        *}"id":0,{*
        *}"parent_id":0,{*
        *}"has_children":true,{*
        *}"keyword":"{"Top level tags"|i18n('extension/eztags/tags/treemenu')|wash(javascript)}",{*
        *}"url":{'tags/dashboard'|ezurl},{*
        *}"icon":"{ezini( 'Icons', 'Default', 'eztags.ini' )|tag_icon}",{*
        *}"modified":currentDate{rdelim};

    document.writeln( '<ul id="content_tree_menu">' );
    document.writeln( treeMenu_0.generateEntry( rootTag, false, true ) );
    document.writeln( '<\/ul>' );

    treeMenu_0.load( false, 0, currentDate );
{rdelim})();
</script>
