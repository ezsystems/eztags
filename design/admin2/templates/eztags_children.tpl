{def $item_type = ezpreference( 'admin_eztags_list_limit' )}
{def $number_of_items = min( $item_type, 3)|choose( 10, 10, 25, 50 )}

{def $children = fetch( tags, list, hash( parent_tag_id, first_set( $tag.id, 0 ),
                                          offset, first_set( $view_parameters.offset, 0 ),
                                          limit, $number_of_items ) )}

{def $children_count = fetch( tags, list_count, hash( parent_tag_id, first_set( $tag.id, 0 ) ) )}

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

    {if $children_count|gt(0)}
        <div class="box-content">
            <div class="context-toolbar">
                <div class="button-left">
                    <p class="table-preferences">
                        {switch match=$number_of_items}
                        {case match=25}
                        <a href={'/user/preferences/set/admin_eztags_list_limit/1'|ezurl} title="{'Show %1 tags per page.'|i18n( 'extension/eztags/tags/dashboard',, array( '10' ) )}">10</a>
                        <span class="current">25</span>
                        <a href={'/user/preferences/set/admin_eztags_list_limit/3'|ezurl} title="{'Show %1 tags per page.'|i18n( 'extension/eztags/tags/dashboard',, array( '50' ) )}">50</a>
                        {/case}

                        {case match=50}
                        <a href={'/user/preferences/set/admin_eztags_list_limit/1'|ezurl} title="{'Show %1 tags per page.'|i18n( 'extension/eztags/tags/dashboard',, array( '10' ) )}">10</a>
                        <a href={'/user/preferences/set/admin_eztags_list_limit/2'|ezurl} title="{'Show %1 tags per page.'|i18n( 'extension/eztags/tags/dashboard',, array( '25' ) )}">25</a>
                        <span class="current">50</span>
                        {/case}

                        {case}
                        <span class="current">10</span>
                        <a href={'/user/preferences/set/admin_eztags_list_limit/2'|ezurl} title="{'Show %1 tags per page.'|i18n( 'extension/eztags/tags/dashboard',, array( '25' ) )}">25</a>
                        <a href={'/user/preferences/set/admin_eztags_list_limit/3'|ezurl} title="{'Show %1 tags per page.'|i18n( 'extension/eztags/tags/dashboard',, array( '50' ) )}">50</a>
                        {/case}
                        {/switch}
                    </p>
                </div>
                <div class="button-right"></div>
                <div class="float-break"></div>
            </div>

            <table class="list" cellspacing="0" border="0">
                <tbody>
                    <tr>
                        <th class="tight">&nbsp;</th>
                        <th>{"ID"|i18n( "extension/eztags/tags/dashboard" )}</th>
                        <th>{"Tag name"|i18n( "extension/eztags/tags/dashboard" )}</th>
                        <th>{"Modified"|i18n( "extension/eztags/tags/dashboard" )}</th>
                        <th class="tight">&nbsp;</th>
                    </tr>
                    {foreach $children as $child_tag sequence array('bglight', 'bgdark') as $sequence}
                        <tr class="{$sequence}">
                            <td><img class="transparent-png-icon" src={concat( 'tag_icons/small/', $child_tag.icon )|ezimage} alt="{$child_tag.keyword|wash}" /></td>                        
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
        </div>
    {else}
        <div class="block">
            <p>{'The current tag does not contain any children.'|i18n( 'extension/eztags/tags/dashboard' )}</p>
        </div>
    {/if}
</div>

{undef}