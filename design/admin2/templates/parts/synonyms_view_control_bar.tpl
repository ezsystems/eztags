<div id="controlbar-top" class="controlbar">
    <div class="box-bc"><div class="box-ml">
        <div class="button-left">
            {if fetch( user, has_access_to, hash( module, tags, function, editsynonym ) )}
                <form name="editsynonym" id="editsynonym" style="float:left; margin-right:10px;" enctype="multipart/form-data" method="post" action={concat( 'tags/editsynonym/', $tag.id )|ezurl}>
                    <input class="button" type="submit" name="SubmitButton" value="{"Edit synonym"|i18n( "extension/eztags/tags/edit" )}" />
                </form>
            {/if}
            {if fetch( user, has_access_to, hash( module, tags, function, deletesynonym ) )}
                <form name="tagdelete" id="tagdelete" style="float:left;" enctype="multipart/form-data" method="post" action={concat( 'tags/deletesynonym/', $tag.id )|ezurl}>
                    <input class="button" type="submit" name="SubmitButton" value="{"Delete synonym"|i18n( "extension/eztags/tags/edit" )}" />
                </form>
            {/if}
        </div>
        <div class="float-break"></div>
    </div></div>
</div>