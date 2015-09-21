{def $show_legacy_children_list = ezini( 'GeneralSettings', 'ShowOldStyleChildrenList', 'eztags.ini' )|eq( 'enabled' )}

<div class="controlbar">
    <div class="button-left">
        <div class="block">
            {if and( $show_legacy_children_list, fetch( user, has_access_to, hash( module, tags, function, add ) ) )}
                <form name="tagadd" id="tagadd" style="float:left; margin-right:10px;" enctype="multipart/form-data" method="post" action={concat( 'tags/add/', $tag.id )|ezurl}>
                    <input class="defaultbutton" type="submit" name="SubmitButton" value="{"Add child tag"|i18n( "extension/eztags/tags/view" )}" />
                </form>
            {/if}
            {if fetch( user, has_access_to, hash( module, tags, function, edit ) )}
                <form name="tagedit" id="tagedit" style="float:left; margin-right:10px;" enctype="multipart/form-data" method="post" action={concat( 'tags/edit/', $tag.id )|ezurl}>
                    <input class="{if $show_legacy_children_list|not}default{/if}button" type="submit" name="SubmitButton" value="{"Edit tag"|i18n( "extension/eztags/tags/view" )}" />
                </form>
            {/if}
            {if fetch( user, has_access_to, hash( module, tags, function, delete ) )}
                <form name="tagdelete" id="tagdelete" style="float:left; margin-right:10px;" enctype="multipart/form-data" method="post" action={concat( 'tags/delete/', $tag.id )|ezurl}>
                    <input class="button" type="submit" name="SubmitButton" value="{"Delete tag"|i18n( "extension/eztags/tags/view" )}" />
                </form>
            {/if}
            {if fetch( user, has_access_to, hash( module, tags, function, merge ) )}
                <form name="tagmerge" id="tagmerge" style="float:left; margin-right:10px;" enctype="multipart/form-data" method="post" action={concat( 'tags/merge/', $tag.id )|ezurl}>
                    <input class="button" type="submit" name="SubmitButton" value="{"Merge tag"|i18n( "extension/eztags/tags/view" )}" />
                </form>
            {/if}
            {if fetch( user, has_access_to, hash( module, tags, function, addsynonym ) )}
                <form name="tagaddsynonym" id="tagaddsynonym" style="float:left; margin-right:10px;" enctype="multipart/form-data" method="post" action={concat( 'tags/addsynonym/', $tag.id )|ezurl}>
                    <input class="button" type="submit" name="SubmitButton" value="{"Add synonym"|i18n( "extension/eztags/tags/view" )}" />
                </form>
            {/if}
            {if fetch( user, has_access_to, hash( module, tags, function, makesynonym ) )}
                <form name="tagmakesynonym" id="tagmakesynonym" style="float:left;" enctype="multipart/form-data" method="post" action={concat( 'tags/makesynonym/', $tag.id )|ezurl}>
                    <input class="button" type="submit" name="SubmitButton" value="{"Convert to synonym"|i18n( "extension/eztags/tags/view" )}" />
                </form>
            {/if}
        </div>
    </div>
    <div class="float-break"></div>
</div>

{undef $show_legacy_children_list}
