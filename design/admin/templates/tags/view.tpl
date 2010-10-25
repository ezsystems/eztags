<div class="context-block">
	<div class="box-header">
		<h1 class="context-title">
			<img class="transparent-png-icon" src={concat('tag_icons/normal/', $tag.icon)|ezimage} alt="{$tag.keyword|wash(xhtml)}" />
			{'Tag'|i18n( 'design/admin/tags/view' )}: {$tag.keyword|wash(xhtml)}
		</h1>
		<div class="header-mainline"></div>
	</div>

	<div class="box-content">
		{include uri='design:parts/tags_view_control_bar.tpl' tag_id=$tag.id}

		{def $nodes=fetch( 'tags', 'node_list',
		                   hash( 'alphabet', $tag.keyword, 'sort_by', array('published', false()) ) )}

		<h2>Latest content</h2>
		{foreach $nodes as $node}
			{node_view_gui content_node=$node view=line}<br />
		{/foreach}
	</div>
</div>