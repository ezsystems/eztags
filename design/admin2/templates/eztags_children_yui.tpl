{def $parent_tag_id = 0}
{if is_set( $tag )}
    {set $parent_tag_id = $tag.id}
{/if}

{def $children_count = fetch( tags, list_count, hash( parent_tag_id, $parent_tag_id ) )}

<div class="context-block">
    <div class="box-header">
        <div class="button-left">
            <h2 class="context-title">
                {if is_set( $tag )}<a href={$tag.depth|gt( 1 )|choose( '/tags/dashboard'|ezurl, concat( '/tags/id/', $tag.parent.id )|ezurl )} title="{'Up one level.'|i18n(  'extension/eztags/tags/view'  )}"><img src={'up-16x16-grey.png'|ezimage} alt="{'Up one level.'|i18n( 'extension/eztags/tags/view' )}" title="{'Up one level.'|i18n( 'extension/eztags/tags/view' )}" /></a>&nbsp;{/if}{'Children tags (%children_count)'|i18n( 'extension/eztags/tags/view',, hash( '%children_count', $children_count ) )}
            </h2>
        </div>
        <div class="button-right button-header"></div>
        <div class="float-break"></div>
    </div>

    <div class="box-content">
        <div id="action-controls-container">
            <div id="action-controls"></div>
            <div id="tpg"></div>
        </div>
        <form id="eztags-children-actions" method="post" enctype="multipart/form-data">
            <div id="eztags-tag-children-table"></div>
        </form>
        <div id="bpg"></div>

        {def $languages = fetch( content, prioritized_languages )}
        <script type="text/javascript">
            var languages = {ldelim}{*
                *}{foreach $languages as $language}{*
                    *}'{$language.locale|wash( javascript )}': {ldelim}{*
                        *}name: '{$language.locale_object.intl_language_name|wash( javascript )}',{*
                        *}flag: '{$language.locale|flag_icon}'{*
                    *}{rdelim}{*
                    *}{delimiter}, {/delimiter}{*
                *}{/foreach}{*
            *}{rdelim};

            var i18n = {ldelim}{*
                *}id: "{'ID'|i18n( 'extension/eztags/tags/view' )|wash( javascript )}",{*
                *}tag_name: "{'Tag name'|i18n( 'extension/eztags/tags/view' )|wash( javascript )}",{*
                *}translations: "{'Tag translations'|i18n( 'extension/eztags/tags/view' )|wash( javascript )}",{*
                *}modified: "{'Modified'|i18n( 'extension/eztags/tags/view' )|wash( javascript )}",{*
                *}first_page: "&laquo;&nbsp;{'first'|i18n( 'extension/eztags/tags/view' )|wash( javascript )}",{*
                *}last_page: "{'last'|i18n( 'extension/eztags/tags/view' )|wash( javascript )}&nbsp;&raquo;",{*
                *}previous_page: "&lsaquo;&nbsp;{'prev'|i18n( 'extension/eztags/tags/view' )|wash( javascript )}",{*
                *}next_page: "{'next'|i18n( 'extension/eztags/tags/view' )|wash( javascript )}&nbsp;&rsaquo;",{*
                *}select_visible: "{'Select all visible'|i18n( 'extension/eztags/tags/view' )|wash( javascript )}",{*
                *}select_none: "{'Select none'|i18n( 'extension/eztags/tags/view' )|wash( javascript )}",{*
                *}select_toggle: "{'Invert selection'|i18n( 'extension/eztags/tags/view' )|wash( javascript )}",{*
                *}select: "{'Select'|i18n( 'extension/eztags/tags/view' )|wash( javascript )}",{*
                *}add_child: "{'Add tag'|i18n( 'extension/eztags/tags/view' )|wash( javascript )}",{*
                *}add_child_group: "{'Translation'|i18n( 'extension/eztags/tags/view' )|wash( javascript )}",{*
                *}more_actions: "{'More actions'|i18n( 'extension/eztags/tags/view' )|wash( javascript )}",{*
                *}more_actions_denied: "{'You do not have permissions for any of available actions'|i18n( 'extension/eztags/tags/view' )|wash( javascript )}",{*
                *}remove_selected: "{'Remove selected tags'|i18n( 'extension/eztags/tags/view' )|wash( javascript )}",{*
                *}move_selected: "{'Move selected tags'|i18n( 'extension/eztags/tags/view' )|wash( javascript )}",{*
                *}no_actions: "{'Use the checkboxes to select one or more tags.'|i18n( 'extension/eztags/tags/view' )|wash( javascript )}"{*
            *}{rdelim};

            var permissions = {ldelim}{*
                *}add: {if fetch( user, has_access_to, hash( module, tags, function, add ) )}true{else}false{/if},{*
                *}edit: {if fetch( user, has_access_to, hash( module, tags, function, edit ) )}true{else}false{/if},{*
                *}delete: {if fetch( user, has_access_to, hash( module, tags, function, delete ) )}true{else}false{/if}{*
            *}{rdelim};

            var urls = {ldelim}{*
                *}yui2: {ezini( 'eZJSCore', 'LocalScriptBasePath', 'ezjscore.ini' )['yui2']|ezdesign},{*
                *}data: {concat( '/ezjscore/call/ezjsctagschildren::tagsChildren::', $parent_tag_id, '?' )|ezurl},{*
                *}view: {'/tags/id/'|ezurl},{*
                *}add: {concat( '/tags/add/', $parent_tag_id )|ezurl},{*
                *}edit: {'/tags/edit/'|ezurl},{*
                *}deletetags: {'/tags/deletetags'|ezurl},{*
                *}movetags: {'/tags/movetags'|ezurl}{*
            *}{rdelim};

            jQuery(document).ready(function($) {ldelim}
                $('#eztags-tag-children-table').eZTagsChildren({ldelim}
                    rowsPerPage: 10,
                    languages: languages,
                    permissions: permissions,
                    urls: urls,
                    i18n: i18n
                {rdelim});
            {rdelim});
        </script>
    </div>
</div>

{undef}
