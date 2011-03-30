{if and( is_set( $module_result.ui_context ), $module_result.ui_context|eq( 'navigation' ), fetch( user, has_access_to, hash( module, tags, function, read ) ) )}
<div id="content-tree">
<div class="box-header"><div class="box-ml">
<h4>{'Tags structure'|i18n( 'extension/eztags/tags/treemenu' )}</h4>
</div></div>

<div class="box-bc"><div class="box-ml"><div class="box-content">

<div id="contentstructure">
    {include uri='design:tagsstructuremenu/tags_structure_menu_dynamic.tpl' menu_persistence=true()}
</div>

</div></div></div>
</div>

<div id="widthcontrol-links" class="widthcontrol">
<p>
{switch match=ezpreference( 'admin_left_menu_size' )}
    {case match='medium'}
    <a href={'/user/preferences/set/admin_left_menu_size/small'|ezurl} title="{'Change the left menu width to small size.'|i18n( 'design/admin/parts/user/menu' )}">{'Small'|i18n( 'design/admin/parts/user/menu' )}</a>
    <span class="current">{'Medium'|i18n( 'design/admin/parts/user/menu' )}</span>
    <a href={'/user/preferences/set/admin_left_menu_size/large'|ezurl} title="{'Change the left menu width to large size.'|i18n( 'design/admin/parts/user/menu' )}">{'Large'|i18n( 'design/admin/parts/user/menu' )}</a>
    {/case}

    {case match='large'}
    <a href={'/user/preferences/set/admin_left_menu_size/small'|ezurl} title="{'Change the left menu width to small size.'|i18n( 'design/admin/parts/user/menu' )}">{'Small'|i18n( 'design/admin/parts/user/menu' )}</a>
    <a href={'/user/preferences/set/admin_left_menu_size/medium'|ezurl} title="{'Change the left menu width to medium size.'|i18n( 'design/admin/parts/user/menu' )}">{'Medium'|i18n( 'design/admin/parts/user/menu' )}</a>
    <span class="current">{'Large'|i18n( 'design/admin/parts/user/menu' )}</span>
    {/case}

    {case in=array( 'small', '' )}
    <span class="current">{'Small'|i18n( 'design/admin/parts/user/menu' )}</span>
    <a href={'/user/preferences/set/admin_left_menu_size/medium'|ezurl} title="{'Change the left menu width to medium size.'|i18n( 'design/admin/parts/user/menu' )}">{'Medium'|i18n( 'design/admin/parts/user/menu' )}</a>
    <a href={'/user/preferences/set/admin_left_menu_size/large'|ezurl} title="{'Change the left menu width to large size.'|i18n( 'design/admin/parts/user/menu' )}">{'Large'|i18n( 'design/admin/parts/user/menu' )}</a>
    {/case}

    {case}
    <a href={'/user/preferences/set/admin_left_menu_size/small'|ezurl} title="{'Change the left menu width to small size.'|i18n( 'design/admin/parts/user/menu' )}">{'Small'|i18n( 'design/admin/parts/user/menu' )}</a>
    <a href={'/user/preferences/set/admin_left_menu_size/medium'|ezurl} title="{'Change the left menu width to medium size.'|i18n( 'design/admin/parts/user/menu' )}">{'Medium'|i18n( 'design/admin/parts/user/menu' )}</a>
    <a href={'/user/preferences/set/admin_left_menu_size/large'|ezurl} title="{'Change the left menu width to large size.'|i18n( 'design/admin/parts/user/menu' )}">{'Large'|i18n( 'design/admin/parts/user/menu' )}</a>
    {/case}
{/switch}
</p>
</div>

{* This is the border placed to the left for draging width, js will handle disabling the one above and enabling this *}
<div id="widthcontrol-handler" class="hide">
<div class="widthcontrol-grippy"></div>
</div>

<!-- script type="text/javascript" src={"javascript/leftmenu_widthcontrol.js"|ezdesign} charset="utf-8"></script -->
{/if}