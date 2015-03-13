{def $item_type = ezpreference( 'admin_eztags_list_limit' )}
{def $number_of_items = min( $item_type, 3)|choose( 10, 10, 25, 50 )}

{def $children = fetch( tags, list, hash( parent_tag_id, first_set( $tag.id, 0 ),
                                          offset, first_set( $view_parameters.offset, 0 ),
                                          limit, $number_of_items ) )}

{def $children_count = fetch( tags, list_count, hash( parent_tag_id, first_set( $tag.id, 0 ) ) )}

<div class="context-block">
    <div class="box-header">
        <h2 class="context-title">
            {if is_set($tag)}<a href={$tag.depth|gt(1)|choose( '/tags/dashboard'|ezurl, concat( '/tags/id/', $tag.parent.id )|ezurl )} title="{'Up one level.'|i18n(  'extension/eztags/tags/view'  )}"><img src={'up-16x16-grey.png'|ezimage} alt="{'Up one level.'|i18n( 'extension/eztags/tags/view' )}" title="{'Up one level.'|i18n( 'extension/eztags/tags/view' )}" /></a>&nbsp;{/if}{'Children tags (%children_count)'|i18n( 'extension/eztags/tags/view',, hash( '%children_count', $children_count ) )}
        </h2>
        <div class="float-break"></div>
    </div>

    <div class="box-content">
        {if $children_count|gt(0)}
            <div class="context-toolbar">
                <div class="button-left">
                    <p class="table-preferences">
                        {switch match=$number_of_items}
                        {case match=25}
                        <a href={'/user/preferences/set/admin_eztags_list_limit/1'|ezurl} title="{'Show %1 tags per page.'|i18n( 'extension/eztags/tags/view',, array( '10' ) )}">10</a>
                        <span class="current">25</span>
                        <a href={'/user/preferences/set/admin_eztags_list_limit/3'|ezurl} title="{'Show %1 tags per page.'|i18n( 'extension/eztags/tags/view',, array( '50' ) )}">50</a>
                        {/case}

                        {case match=50}
                        <a href={'/user/preferences/set/admin_eztags_list_limit/1'|ezurl} title="{'Show %1 tags per page.'|i18n( 'extension/eztags/tags/view',, array( '10' ) )}">10</a>
                        <a href={'/user/preferences/set/admin_eztags_list_limit/2'|ezurl} title="{'Show %1 tags per page.'|i18n( 'extension/eztags/tags/view',, array( '25' ) )}">25</a>
                        <span class="current">50</span>
                        {/case}

                        {case}
                        <span class="current">10</span>
                        <a href={'/user/preferences/set/admin_eztags_list_limit/2'|ezurl} title="{'Show %1 tags per page.'|i18n( 'extension/eztags/tags/view',, array( '25' ) )}">25</a>
                        <a href={'/user/preferences/set/admin_eztags_list_limit/3'|ezurl} title="{'Show %1 tags per page.'|i18n( 'extension/eztags/tags/view',, array( '50' ) )}">50</a>
                        {/case}
                        {/switch}
                    </p>
                </div>
                <div class="float-break"></div>
            </div>

            <table class="list" cellspacing="0">
                <tbody>
                    <tr>
                        <th class="tight">&nbsp;</th>
                        <th>{"ID"|i18n( "extension/eztags/tags/view" )}</th>
                        <th>{"Tag name"|i18n( "extension/eztags/tags/view" )}</th>
                        <th>{"Modified"|i18n( "extension/eztags/tags/view" )}</th>
                        <th class="tight">&nbsp;</th>
                    </tr>
                    {foreach $children as $child_tag sequence array('bglight', 'bgdark') as $sequence}
                        <tr class="{$sequence}">
<<<<<<< HEAD
                            <td><img class="transparent-png-icon" src={concat( 'tag_icons/small/', $child_tag.icon )|ezimage} alt="{$child_tag.keyword|wash}" /></td>
=======
                            <td><img class="transparent-png-icon" src="{$child_tag.icon|tag_icon}" alt="{$child_tag.keyword|wash}" /></td>
>>>>>>> 06abc6e4d24cb0184dd64c8a211ac25dcafa5b1b
                            <td>{$child_tag.id}</td>
                            <td><a href={concat( '/tags/id/', $child_tag.id )|ezurl}>{$child_tag.keyword|wash}{cond( $child_tag.synonyms_count|gt(0), concat( ' (+', $child_tag.synonyms_count, ')' ), '' )}</a></td>
                            <td>{$child_tag.modified|datetime( 'custom', '%d.%m.%Y %H:%i' )}</td>
                            <td><a href={concat( '/tags/edit/', $child_tag.id )|ezurl}><img src={'edit.gif'|ezimage} alt="Edit" /></a></td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>

            <div class="context-toolbar subitems-context-toolbar">
                {include uri='design:navigator/google.tpl'
                         page_uri=cond( is_set( $tag ), concat( '/tags/id/', $tag.id ), '/tags/dashboard' )
                         item_count=$children_count
                         view_parameters=$view_parameters
                         item_limit=$number_of_items}
            </div>
        {else}
            <p>{'The current tag does not contain any children.'|i18n( 'extension/eztags/tags/view' )}</p>
        {/if}
    </div>
</div>

{undef}
