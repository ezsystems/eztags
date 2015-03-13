<div class="context-block tags-edit">
    <div class="box-header">
        <h1 class="context-title">{"New synonym tag"|i18n( 'extension/eztags/tags/edit' )}</h1>
        <div class="header-mainline"></div>
    </div>

    <div class="box-content">
        <form method="post" action={concat( 'tags/addsynonym/', $main_tag.id )|ezurl}>
            <div class="block">
                <fieldset>
                    <legend>{'Add translation'|i18n('extension/eztags/tags/edit')}</legend>
                    <p>{'Select the translation you want to add'|i18n('extension/eztags/tags/edit')}:</p>
                    <div class="indent">
                        {foreach $languages as $index => $language}
                            <label><input name="Locale" type="radio" value="{$language.locale|wash}" {if $index|eq(0)}checked="checked"{/if} > {$language.name|wash}</label>
                        {/foreach}
                   </div>
                </fieldset>
            </div>
            <div class="controlbar">
                <div class="block">
                    <input class="defaultbutton" type="submit" name="AddTranslationButton" value="{'New synonym tag'|i18n( 'extension/eztags/tags/edit' )}" />
                    <input class="button" type="submit" name="DiscardButton" value="{'Discard'|i18n( 'extension/eztags/tags/edit' )}" />
                </div>
            </div>
        </form>
    </div>
</div>
