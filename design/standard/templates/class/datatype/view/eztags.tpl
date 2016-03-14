{def $available_edit_views = ezini( 'EditSettings', 'AvailableViews', 'eztags.ini' )}

<div class="block">
    <div class="element">
        <label>{'Limit by tags subtree'|i18n( 'extension/eztags/datatypes' )}:</label>
        <p>
            {if $class_attribute.data_int1|eq( 0 )}
                {'No limit'|i18n( 'extension/eztags/datatypes' )}
            {else}
                <a href={concat( 'tags/id/', $class_attribute.data_int1 )|ezurl}>{eztags_parent_string( $class_attribute.data_int1 )|wash}</a>
            {/if}
        </p>
    </div>

    <div class="element">
        <label>{'Hide root subtree limit tag when editing object'|i18n( 'extension/eztags/datatypes' )}:</label>
        <p>{cond( $class_attribute.data_int3|eq( 0 ), 'No'|i18n( 'extension/eztags/datatypes' ), 'Yes'|i18n( 'extension/eztags/datatypes' ) )}</p>
    </div>

    <div class="element">
        <label>{'Maximum number of allowed tags'|i18n( 'extension/eztags/datatypes' )}:</label>
        <p>{cond( $class_attribute.data_int4|gt( 0 ), $class_attribute.data_int4, 'Unlimited'|i18n( 'extension/eztags/datatypes' ) )}</p>
    </div>

    <div class="element">
        <label>{'Edit view'|i18n( 'extension/eztags/datatypes' )}:</label>
        <p>{$available_edit_views[$class_attribute.data_text1]|wash|i18n( 'extension/eztags/datatypes' )}</p>
    </div>

    <div class="break"></div>
</div>
