{ezscript_require( array( 'ezjsc::jquery', 'ezjsc::jqueryio', 'ezjsc::yui2' ) )}

{def $parent_tag_id = 0}
{if is_set( $tag )}
    {set $parent_tag_id = $tag.id}
{/if}

{def $children_count = fetch( tags, list_count, hash( parent_tag_id, $parent_tag_id ) )}

<div class="context-block">
    <div class="box-header">
        <div class="button-left">
            <h2 class="context-title">
                {if is_set($tag)}<a href={$tag.depth|gt(1)|choose( '/tags/dashboard'|ezurl, concat( '/tags/id/', $tag.parent.id )|ezurl )} title="{'Up one level.'|i18n(  'extension/eztags/tags/dashboard'  )}"><img src={'up-16x16-grey.png'|ezimage} alt="{'Up one level.'|i18n( 'extension/eztags/tags/dashboard' )}" title="{'Up one level.'|i18n( 'extension/eztags/tags/dashboard' )}" /></a>&nbsp;{/if}{'Children tags (%children_count)'|i18n( 'extension/eztags/tags/dashboard',, hash( '%children_count', $children_count ) )}
            </h2>
        </div>
        <div class="button-right button-header"></div>
        <div class="float-break"></div>
    </div>

    <div class="box-content">
        {def $locales = fetch( 'content', 'translation_list' )}

        <div id="eztags-tag-children-table"></div>
        <div id="eztags-tag-children-paging"></div>

        {def $yui2_base_path = ezini( 'eZJSCore', 'LocalScriptBasePath', 'ezjscore.ini' )}
        {set $yui2_base_path = concat( '/extension/ezjscore/design/standard/', $yui2_base_path['yui2'] )}

        <script type="text/javascript">
            var languages = {ldelim}{*
                *}{foreach $locales as $language}{*
                    *}'{$language.locale_code|wash(javascript)}': '{$language.intl_language_name|wash(javascript)}'{*
                    *}{delimiter}, {/delimiter}{*
                *}{/foreach}{*
            *}{rdelim};

            var icons = {ldelim}{*
                *}{foreach $locales as $locale}{*
                    *}'{$locale.locale_code}': '{$locale.locale_code|flag_icon()}'{*
                    *}{delimiter}, {/delimiter}{*
                *}{/foreach}{*
            *}{rdelim};

            jQuery(document).ready(function($) {ldelim}
                $('#eztags-tag-children-table').eZTagsChildren({ldelim}
                    YUI2BasePath: "{$yui2_base_path}",
                    parentTagID: {if is_set( $tag )}{$parent_tag_id}{else}0{/if},
                    rowsPerPage: 10,
                    languages: languages,
                    editUrl: {'/tags/edit/'|ezurl},
                    icons: icons
                {rdelim});
            {rdelim});
        </script>
    </div>
</div>

{undef}