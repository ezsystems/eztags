<div class="block">
	<div class="element">
		<label>{'Limit by tags subtree'|i18n( 'design/standard/class/datatype' )}:</label>
		<p>
			{if $class_attribute.data_int1|eq(0)}
				{'No limit'|i18n( 'design/standard/class/datatype' )}
			{else}
				<a href={concat('tags/id/', $class_attribute.data_int1)|ezurl}>{eztags_parent_string($class_attribute.data_int1)|wash(xhtml)}</a>
			{/if}
		</p>
	</div>

	<div class="element">
		<label>{'Show dropdown instead of autocomplete'|i18n( 'design/standard/class/datatype' )}:</label>
		<p>{cond($class_attribute.data_int2|eq(0), 'No', 'Yes')|i18n( 'design/standard/class/datatype' )}</p>
	</div>

    <div class="break"></div>
</div>