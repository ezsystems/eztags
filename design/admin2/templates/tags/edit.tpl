<<<<<<< HEAD
{ezcss_require( array( 'jqmodal.css', 'contentstructure-tree.css' ) )}
{ezscript_require( array( 'jquery-migrate-1.1.1.min.js', 'jqmodal.js', 'eztagsselectparent.js' ) )}
=======
{def $is_main_translation = $tag.main_translation.locale|eq( $language.locale )}
>>>>>>> 06abc6e4d24cb0184dd64c8a211ac25dcafa5b1b

<div class="context-block tags-edit">
    <div class="box-header">
        <h1 class="context-title">{"Edit tag"|i18n( 'extension/eztags/tags/edit' )}: {$tag.keyword|wash} [{$tag.id}]</h1>
        <p><img src="{$language.locale|flag_icon}" title="{$language.name|wash}" /> {$language.name|wash}</p>
        <p>{if $is_main_translation|not}{'Main translation'|i18n( 'extension/eztags/tags/edit' )}: {$tag.main_translation.keyword|wash}{/if}</p>
        <div class="header-mainline"></div>
    </div>

    {if $error|count}
        <div class="message-error">
            <h2>{$error|wash}</h2>
        </div>
    {/if}

    {if $warning|count}
        <div class="message-warning">
            <h2>{$warning|wash}</h2>
        </div>
    {/if}

    <div class="box-content">
        <form name="tageditform" id="tageditform" enctype="multipart/form-data" method="post" action={concat( 'tags/edit/', $tag.id )|ezurl}>
            <div class="block tag-edit-keyword">
                <label>{'Tag name'|i18n( 'extension/eztags/tags/edit' )}</label>
                <input id="keyword" class="halfbox" type="text" size="70" name="TagEditKeyword" value="{cond( ezhttp_hasvariable( 'TagEditKeyword', 'post' ), ezhttp( 'TagEditKeyword', 'post' ), $tag.keyword )|trim|wash}" />
                <input type="hidden" name="Locale" value="{$language.locale|wash}" />
                {if $is_main_translation|not}
                    <label><input type="checkbox" name="SetAsMainTranslation" /> {'Set as main translation'|i18n( 'extension/eztags/tags/edit' )}</label>
                {/if}
                <label><input type="checkbox" name="AlwaysAvailable" {if $tag.always_available}checked="checked"{/if} /> {'Use the main language if there is no prioritized translation.'|i18n( 'extension/eztags/tags/edit' )}</label>
            </div>

            <div class="block tag-edit-parent">
                {if ezhttp_hasvariable( 'TagEditParentID', 'post' )}
                    {def $parent_tag_id = ezhttp( 'TagEditParentID', 'post' )}
                {else}
                    {def $parent_tag_id = $tag.parent_id}
                {/if}

                <label>{'Parent tag'|i18n( 'extension/eztags/tags/edit' )}</label>
                <input id="eztags_parent_id_0" type="hidden" name="TagEditParentID" value="{$parent_tag_id}" />
                <input id="hide_tag_id_0" type="hidden" name="TagHideID" value="{$tag.id}" />
                <span id="eztags_parent_keyword_0">{eztags_parent_string( $parent_tag_id )|wash}</span>
                {if $is_main_translation}
                    <input class="button" type="button" name="SelectParentButton" id="eztags-parent-selector-button-0" value="{'Select parent'|i18n( 'extension/eztags/tags/edit' )}" />
                {/if}
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

{if $is_main_translation}
    {include uri='design:ezjsctemplate/modal_dialog.tpl'}
{/if}

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
<<<<<<< HEAD
=======

{undef $is_main_translation $parent_tag_id}
>>>>>>> 06abc6e4d24cb0184dd64c8a211ac25dcafa5b1b
