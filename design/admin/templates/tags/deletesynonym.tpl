<div class="context-block tags-delete">
    <div class="box-header">
        <h1 class="context-title">{"Delete synonym"|i18n( 'extension/eztags/tags/edit' )}: {$tag.keyword|wash} [{$tag.id}]</h1>
        <div class="header-mainline"></div>
    </div>

    <div class="box-content">
        <form name="tagdeleteform" id="tagdeleteform" enctype="multipart/form-data" method="post" action={concat( 'tags/deletesynonym/', $tag.id )|ezurl}>
            <p>{'Are you sure you want to delete the "%keyword" synonym?'|i18n( 'extension/eztags/tags/edit', , hash( '%keyword', $tag.keyword|wash ) )}</p>

            <p><label for="TransferObjectsToMainTag"><input type="checkbox" id="TransferObjectsToMainTag" name="TransferObjectsToMainTag" checked="checked" /> {'Transfer all related objects to the main tag'|i18n( 'extension/eztags/tags/edit' )}</label></p>

            <div class="controlbar">
                <div class="block">
                    <input class="defaultbutton" type="submit" name="YesButton" value="{'Yes'|i18n( 'extension/eztags/tags/edit' )}" />
                    <input class="button" type="submit" name="NoButton" value="{'No'|i18n( 'extension/eztags/tags/edit' )}" />
                </div>
            </div>
        </form>
    </div>
</div>