<div class="context-block tags-add">
    <div class="box-header">
        <h1 class="context-title">{"New tag"|i18n( 'extension/eztags/tags/edit' )}</h1>
        <p><img src="{$language.locale|flag_icon}" title="{$language.name|wash}" /> {$language.name|wash}</p>
        <div class="header-mainline"></div>
    </div>

    {if $error|count}
        <div class="message-error">
            <h2>{$error|wash}</h2>
        </div>
    {/if}

    <div class="box-content">
        {if ezhttp_hasvariable( 'TagEditParentID', 'post' )}
            {def $parent_tag_id = ezhttp( 'TagEditParentID', 'post' )}
        {else}
            {def $parent_tag_id = $parent_id}
        {/if}

        {def $always_available = ezini( 'GeneralSettings', 'DefaultAlwaysAvailable', 'eztags.ini' )|eq( 'true' )}

        <form name="tageditform" id="tageditform" enctype="multipart/form-data" method="post" action={concat( 'tags/add/', $parent_tag_id )|ezurl}>
            <div class="block tag-edit-keyword">
                <label>{'Tag name'|i18n( 'extension/eztags/tags/edit' )}</label>
                <input id="keyword" class="halfbox" type="text" size="70" name="TagEditKeyword" value="{cond( ezhttp_hasvariable( 'TagEditKeyword', 'post' ), ezhttp( 'TagEditKeyword', 'post' ), '' )|trim|wash}" />
                <label><input type="checkbox" name="AlwaysAvailable" {if $always_available}checked="checked"{/if} /> {'Use the main language if there is no prioritized translation.'|i18n( 'extension/eztags/tags/edit' )}</label>
                <input type="hidden" name="Locale" value="{$language.locale|wash}" />
            </div>

            <div class="block tag-edit-parent">
                <label>{'Parent tag'|i18n( 'extension/eztags/tags/edit' )}</label>
                <input id="eztags_parent_id_0" type="hidden" name="TagEditParentID" value="{$parent_tag_id}" />
                <input id="hide_tag_id_0" type="hidden" name="TagHideID" value="-1" />
                <span id="eztags_parent_keyword_0">{eztags_parent_string( $parent_tag_id )|wash}</span>
                <input class="button" type="button" name="SelectParentButton" id="eztags-parent-selector-button-0" value="{'Select parent'|i18n( 'extension/eztags/tags/edit' )}" />
            </div>

            <div class="controlbar">
                <div class="block">
                    <input class="defaultbutton" type="submit" name="SaveButton" value="{'Save'|i18n( 'extension/eztags/tags/edit' )}" />
                    <input class="button" type="submit" name="DiscardButton" value="{'Discard'|i18n( 'extension/eztags/tags/edit' )}" onclick="return confirmDiscard( '{'Are you sure you want to discard changes?'|i18n( 'extension/eztags/tags/edit' )|wash( javascript )}' );" />
                    <input type="hidden" name="DiscardConfirm" value="1" />
                </div>
            </div>
        </form>
    </div>
</div>

{include uri='design:ezjsctemplate/modal_dialog.tpl'}

{literal}
<script language="JavaScript" type="text/javascript">
function confirmDiscard( question )
{
    // Disable/bypass the reload-based (plain HTML) confirmation interface.
    document.tageditform.DiscardConfirm.value = "0";

    // Ask user if she really wants do it, return this to the handler.
    return confirm( question );
}
</script>
{/literal}

{undef $parent_tag_id $always_available}
