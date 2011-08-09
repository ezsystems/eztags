<div class="block">
    <div class="element">
        <label>{'Limit by tags subtree'|i18n( 'design/standard/class/datatype' )}:</label>
        <p>
            {if $class_attribute.data_int1|eq( 0 )}
                {'No limit'|i18n( 'design/standard/class/datatype' )}
            {else}
                <a href={concat( 'tags/id/', $class_attribute.data_int1 )|ezurl}>{eztags_parent_string( $class_attribute.data_int1 )|wash}</a>
            {/if}
        </p>
    </div>

    <div class="element">
        <label>{'Hide root subtree limit tag when editing object'|i18n( 'design/standard/class/datatype' )}:</label>
        <p>{cond( $class_attribute.data_int3|eq( 0 ), 'No'|i18n( 'design/standard/class/datatype' ), 'Yes'|i18n( 'design/standard/class/datatype' ) )}</p>
    </div>

    <div class="element">
        <label>{'Show dropdown instead of autocomplete'|i18n( 'design/standard/class/datatype' )}:</label>
        <p>{cond( $class_attribute.data_int2|eq( 0 ), 'No'|i18n( 'design/standard/class/datatype' ), 'Yes'|i18n( 'design/standard/class/datatype' ) )}</p>
    </div>

    <div class="element">
        <label>{'Maximum number of allowed tags'|i18n( 'design/standard/class/datatype' )}:</label>
        <p>{cond( $class_attribute.data_int4|gt( 0 ), $class_attribute.data_int4, 'Unlimited'|i18n( 'design/standard/class/datatype' ) )}</p>
    </div>

    <div class="break"></div>
</div>
