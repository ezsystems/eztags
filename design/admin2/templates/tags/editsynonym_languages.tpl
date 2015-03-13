<div class="context-block tags-edit">
    <div class="box-header">
        <h1 class="context-title">{"Edit synonym"|i18n( 'extension/eztags/tags/edit' )}: {$tag.keyword|wash} [{$tag.id}]</h1>
        <div class="header-mainline"></div>
    </div>

    <div class="box-content">
        <form method="post" action={concat( 'tags/editsynonym/', $tag.id )|ezurl}>
            <div class="block">
                <fieldset>
                    <legend>{'Edit translation'|i18n('extension/eztags/tags/edit')}</legend>
                    <p>{'Select the translation you want to edit'|i18n('extension/eztags/tags/edit')}:</p>
                    <div class="indent">
                        {def $is_main_translation = false()}
                        {def $available_languages = $tag.available_languages}
                        {foreach $languages as $language}
                            {if $available_languages|contains( $language.locale )}
                                {set $is_main_translation = cond($tag.main_translation.locale|eq($language.locale), true(), false())}
                                <label><input name="Locale" type="radio" value="{$language.locale|wash}"{if $is_main_translation} checked="checked"{/if}> {$language.name|wash}{if $is_main_translation} ({'Main translation'|i18n('extension/eztags/tags/edit')}){/if}</label>
                            {else}
                                {append-block variable=$new_translations}
                                    <label><input name="Locale" type="radio" value="{$language.locale|wash}"> {$language.name|wash}</label>
                                {/append-block}
                            {/if}
                        {/foreach}
                        {undef $is_main_translation $available_languages}
                    </div>
                </fieldset>
            </div>

            {if $new_translations|count}
                <div class="block">
                    <fieldset>
                        <legend>{'Add translation'|i18n('extension/eztags/tags/edit')}</legend>
                        <p>{'Select the translation you want to add'|i18n('extension/eztags/tags/edit')}:</p>
                        <div class="indent">
                            {$new_translations|implode( '' )}
                        </div>
                    </fieldset>
                </div>
            {/if}

            <div class="controlbar">
                <div class="block">
                    <input class="defaultbutton" type="submit" name="EditButton" value="{'Edit'|i18n( 'extension/eztags/tags/edit' )}" />
                    <input class="button" type="submit" name="DiscardButton" value="{'Cancel'|i18n( 'extension/eztags/tags/edit' )}" />
                </div>
            </div>
        </form>
    </div>
</div>
