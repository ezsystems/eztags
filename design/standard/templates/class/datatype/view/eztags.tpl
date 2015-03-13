<div class="block">
    <div class="element">
<<<<<<< HEAD
        <label>{'Limit by tags subtree'|i18n( 'design/standard/class/datatype' )}:</label>
        <p>
            {if $class_attribute.data_int1|eq( 0 )}
                {'No limit'|i18n( 'design/standard/class/datatype' )}
=======
        <label>{'Limit by tags subtree'|i18n( 'extension/eztags/datatypes' )}:</label>
        <p>
            {if $class_attribute.data_int1|eq( 0 )}
                {'No limit'|i18n( 'extension/eztags/datatypes' )}
>>>>>>> 06abc6e4d24cb0184dd64c8a211ac25dcafa5b1b
            {else}
                <a href={concat( 'tags/id/', $class_attribute.data_int1 )|ezurl}>{eztags_parent_string( $class_attribute.data_int1 )|wash}</a>
            {/if}
        </p>
    </div>

    <div class="element">
<<<<<<< HEAD
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
=======
        <label>{'Hide root subtree limit tag when editing object'|i18n( 'extension/eztags/datatypes' )}:</label>
        <p>{cond( $class_attribute.data_int3|eq( 0 ), 'No'|i18n( 'extension/eztags/datatypes' ), 'Yes'|i18n( 'extension/eztags/datatypes' ) )}</p>
    </div>

    <div class="element">
        <label>{'Show dropdown instead of autocomplete'|i18n( 'extension/eztags/datatypes' )}:</label>
        <p>{cond( $class_attribute.data_int2|eq( 0 ), 'No'|i18n( 'extension/eztags/datatypes' ), 'Yes'|i18n( 'extension/eztags/datatypes' ) )}</p>
    </div>

    <div class="element">
        <label>{'Maximum number of allowed tags'|i18n( 'extension/eztags/datatypes' )}:</label>
        <p>{cond( $class_attribute.data_int4|gt( 0 ), $class_attribute.data_int4, 'Unlimited'|i18n( 'extension/eztags/datatypes' ) )}</p>
>>>>>>> 06abc6e4d24cb0184dd64c8a211ac25dcafa5b1b
    </div>

    <div class="break"></div>
</div>
