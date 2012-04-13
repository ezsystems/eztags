{def $has_edit_access = fetch( user, has_access_to, hash( module, tags, function, edit ) )}

<form name="translationsform" method="post" action={'tags/translation'|ezurl}>
    <input type="hidden" name="TagID" value="{$tag.id}" />

    <table class="list" cellpadding="0">
        <tbody>
            <tr>
                {if $has_edit_access}
                    <th class="tight"><img src={'toggle-button-16x16.gif'|ezimage} width="16" height="16" alt="{'Invert selection.'|i18n( 'extension/eztags/tags/view' )}" onclick="ezjs_toggleCheckboxes( document.translationsform, 'Locale[]' ); return false;"/></th>
                {/if}
                <th>{'Language'|i18n( 'extension/eztags/tags/view' )}</th>
                <th>{'Translation'|i18n( 'extension/eztags/tags/view' )}</th>
                <th>{'Locale'|i18n( 'extension/eztags/tags/view' )}</th>
                <th class="tight">{'Main'|i18n( 'extension/eztags/tags/view' )}</th>
                {if $has_edit_access}
                    <th class="tight">&nbsp;</th>
                {/if}
            </tr>
            {foreach $tag.translations as $translation}
                <tr>
                    {if $has_edit_access}
                        <td><input type="checkbox" name="Locale[]" value="{$translation.locale}"{if $translation.locale|eq( $tag.main_translation.locale )} disabled="disabled"{/if} /></d>
                    {/if}
                    <td>
                        <img src="{$translation.locale|flag_icon}" width="18" height="12" alt="{$translation.locale}" />&nbsp;
                        {if $translation.locale|eq( $tag.main_translation.locale )}
                            <strong><a href={concat( '/tags/id/', $tag.id, '/', $translation.locale )|ezurl}>{$translation.language_name.name|wash}</a></strong>
                        {else}
                            <a href={concat( '/tags/id/', $tag.id, '/', $translation.locale )|ezurl}>{$translation.language_name.name|wash}</a>
                        {/if}
                    </td>
                    <td>{$translation.keyword|wash}</td>
                    <td>{$translation.locale|wash}</td>
                    <td><input type="radio" {if $has_edit_access|not}disabled="disabled"{/if} {if $translation.locale|eq( $tag.main_translation.locale )} checked="checked" class="main-translation-radio main-translation-radio-initial"{else} class="main-translation-radio"{/if} name="MainLocale" value="{$translation.locale|wash}" /></td>
                    {if $has_edit_access}
                        <td><a href={concat( '/tags/', cond( $tag.main_tag_id|eq( 0 ), 'edit', 'editsynonym' ), '/', $tag.id, '/', $translation.locale )|ezurl}><img src={'edit.gif'|ezimage} width="16" height="16" alt="{'Edit in <%language_name>.'|i18n( 'extension/eztags/tags/view', , hash( '%language_name', $translation.language_name.name ) )|wash}" /></a></td>
                    {/if}
                </tr>
            {/foreach}
        </tbody>
    </table>

    <div class="block">
        {if $has_edit_access}
            <div class="button-left">
                {if $tag.translations|count}
                    <input class="button" type="submit" name="RemoveTranslationButton" value="{'Remove selected'|i18n( 'extension/eztags/tags/view' )}" />
                {else}
                    <input class="button-disabled" type="submit" name="RemoveTranslationButton" value="{'Remove selected'|i18n( 'extension/eztags/tags/view' )}" disabled="disabled" />
                {/if}
            </div>
        {/if}
        {if $has_edit_access}
            <div class="button-right">
                {if $tag.translations|count}
                    <input id="tab-translations-list-set-main" class="button" type="submit" name="UpdateMainTranslationButton" value="{'Set main'|i18n( 'extension/eztags/tags/view' )}" />
                    <script type="text/javascript">
                    {literal}
                    (function( $ ) {
                        $('input.main-translation-radio').change(function() {
                            if ( this.className === 'main-translation-radio' )
                                $('#tab-translations-list-set-main').removeClass('button').addClass('defaultbutton');
                            else
                                $('#tab-translations-list-set-main').removeClass('defaultbutton').addClass('button');
                        });
                    })( jQuery );
                    {/literal}
                    </script>
                {else}
                    <input class="button-disabled" type="submit" name="UpdateMainTranslationButton" value="{'Set main'|i18n( 'extension/eztags/tags/view' )}" disabled="disabled" />
                {/if}
            </div>
        {/if}
        <div class="break"></div>
    </div>

    <div class="block">
        <div class="block">
            <input {if $has_edit_access|not}disabled="disabled"{/if} id="tab-translations-alwaysavailable-checkbox" type="checkbox" name="AlwaysAvailable" value="1"{if $tag.always_available} checked="checked"{/if} /> {'Use the main language if there is no prioritized translation.'|i18n( 'extension/eztags/tags/view' )}
        </div>

        {if $has_edit_access}
            <div class="block">
                <input id="tab-translations-alwaysavailable-btn" class="button" type="submit" name="UpdateAlwaysAvailableButton" value="{'Update'|i18n( 'extension/eztags/tags/view' )}" />
                <script type="text/javascript">
                {literal}
                (function( $ ) {
                    $('#tab-translations-alwaysavailable-checkbox').change(function() {
                        $('#tab-translations-alwaysavailable-btn').removeClass('button').addClass('defaultbutton');
                    });
                })( jQuery );
                {/literal}
                </script>
            </div>
        {/if}
    </div>

</form>
