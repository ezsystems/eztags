<div class="context-block">
	<div class="box-header">
		<h1 class="context-title">
			<img class="transparent-png-icon" src={concat('tag_icons/normal/', $tag.icon)|ezimage} alt="{$tag.keyword|wash(xhtml)}" />
			{'Tag'|i18n( 'extension/eztags/tags/view' )}: {$tag.keyword|wash(xhtml)}
		</h1>
		<div class="header-mainline"></div>
	</div>

	<div class="box-content">
		{include uri='design:parts/tags_view_control_bar.tpl' tag_id=$tag.id}

		{def $nodes = fetch('content', 'tree', hash('parent_node_id', 2,
													'extended_attribute_filter', hash('id', 'TagsAttributeFilter',
														'params', hash('tag_id', $tag.id)),
													'limit', 10,
													'sort_by', array('published', false())))}

		{if $nodes|count}
			<h2>{'Latest content'|i18n( 'extension/eztags/tags/view' )}</h2>
			{foreach $nodes as $node}
				{node_view_gui content_node=$node view=line}<br />
			{/foreach}
		{/if}
	</div>
</div>