{def $available_tag_translations = $tag.available_translations}

<div class="context-block tags-edit">
    <div class="box-header">
        <h1 class="context-title">{"Edit tag"|i18n( 'extension/eztags/tags/edit' )}: {$tag.keyword|wash} [{$tag.id}]</h1>
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
        <form method="post" action={concat( 'tags/edit/', $tag.id )|ezurl}>
            <div class="block">
                <fieldset>
                    <legend>{'Edit translation'|i18n('extension/eztags/tags/edit')}</legend>
                    <p>{'Select the translation you want to edit'|i18n('extension/eztags/tags/edit')}:</p>
                    <div class="indent">
                        {def $is_main_translation = false()}
                        {foreach $languages as $language}
                            {if is_set( $available_tag_translations[$language.locale] )}
                                {set $is_main_translation = cond($tag.main_language_id|eq($language.id), true(), false())}
                                <label><input name="Locale" type="radio" value="{$language.locale}"{if $is_main_translation} checked="checked"{/if}> {$language.name}{if $is_main_translation} ({'Main translation'|i18n('extension/eztags/tags/edit')}){/if}</label>
                            {else}
                                {append-block variable=$new_translations}
                                    <label><input name="Locale" type="radio" value="{$language.locale}"> {$language.name}</label>
                                {/append-block}
                            {/if}
                        {/foreach}
                    </div>
                </fieldset>
            </div>
            <div class="block">
                <fieldset>
                    <legend>{'Add translation'|i18n('extension/eztags/tags/edit')}</legend>
                    <p>{'Select the translation you want to add'|i18n('extension/eztags/tags/edit')}:</p>
                    <div class="indent">
                        {$new_translations|implode( '' )}
                    </div>
                </fieldset>
            </div>
            <div class="controlbar">
                <div class="block">
                    <input class="defaultbutton" type="submit" name="EditButton" value="{'Edit'|i18n( 'extension/eztags/tags/edit' )}" />
                    <input class="button" type="submit" name="DiscardButton" value="{'Cancel'|i18n( 'extension/eztags/tags/edit' )}" />
                </div>
            </div>
        </form>
    </div>
</div>

{undef}