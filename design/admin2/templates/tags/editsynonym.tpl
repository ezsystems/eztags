{def $is_main_translation = $tag.main_translation.locale|eq( $language.locale )}

<div class="context-block tags-edit">
    <div class="box-header">
        <h1 class="context-title">{"Edit synonym"|i18n( 'extension/eztags/tags/edit' )}: {$tag.keyword|wash} [{$tag.id}]</h1>
        <p><img src="{$language.locale|flag_icon}" title="{$language.name|wash}" /> {$language.name|wash}</p>
        <p>{if $is_main_translation|not}{'Main translation'|i18n( 'extension/eztags/tags/edit' )}: {$tag.main_translation.keyword|wash}{/if}</p>
        <div class="header-mainline"></div>
    </div>

    {if $error|count}
        <div class="message-error">
            <h2>{$error|wash}</h2>
        </div>
    {/if}

    <div class="box-content">
        <form name="tageditform" id="tageditform" enctype="multipart/form-data" method="post" action={concat( 'tags/editsynonym/', $tag.id )|ezurl}>
            <div class="block tag-edit-keyword">
                <label>{'Synonym name'|i18n( 'extension/eztags/tags/edit' )}</label>
                <input id="keyword" class="halfbox" type="text" size="70" name="TagEditKeyword" value="{cond( ezhttp_hasvariable( 'TagEditKeyword', 'post' ), ezhttp( 'TagEditKeyword', 'post' ), $tag.keyword )|trim|wash}" />
                <input type="hidden" name="Locale" value="{$language.locale|wash}" />
                {if $is_main_translation|not}
                    <label><input type="checkbox" name="SetAsMainTranslation" /> {'Set as main translation'|i18n( 'extension/eztags/tags/edit' )}</label>
                {/if}
                <label><input type="checkbox" name="AlwaysAvailable" {if $tag.always_available}checked="checked"{/if} /> {'Use the main language if there is no prioritized translation.'|i18n( 'extension/eztags/tags/edit' )}</label>
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

{undef $is_main_translation}
