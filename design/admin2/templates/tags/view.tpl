<div class="context-block">
	<div class="box-header">
		<h1 class="context-title">
			<img class="transparent-png-icon" src={concat('tag_icons/normal/', $tag.icon)|ezimage} alt="{$tag.keyword|wash(xhtml)}" />
			{if $tag.main_tag_id|eq(0)}
				{'Tag'|i18n( 'extension/eztags/tags/view' )}: {$tag.keyword|wash(xhtml)}
			{else}
				{'Synonym'|i18n( 'extension/eztags/tags/view' )}: {$tag.keyword|wash(xhtml)} ({'Main tag'|i18n( 'extension/eztags/tags/view' )}: <a href={concat('tags/id/', $tag.main_tag_id)|ezurl}>{$tag.main_tag.keyword|wash(xhtml)}</a>)
			{/if}
		</h1>
		<div class="header-mainline"></div>
	</div>

	<div class="box-content">
		{if $tag.main_tag_id|eq(0)}
			{include uri='design:parts/tags_view_control_bar.tpl' tag=$tag}
		{else}
			{include uri='design:parts/synonyms_view_control_bar.tpl' tag=$tag}
		{/if}

		<div class="block">
			<div class="left">
				{def $nodes = fetch('content', 'tree', hash('parent_node_id', 2,
															'extended_attribute_filter', hash('id', 'TagsAttributeFilter',
																'params', hash('tag_id', $tag.id, 'include_synonyms', false())),
															'limit', 10,
															'sort_by', array('modified', false())))}

				<h2>{'Latest content'|i18n( 'extension/eztags/tags/view' )}</h2>

				{if $nodes|count}
					<table class="list" cellpadding="0" border="0">
						<tbody>
							<tr>
								<th>{"ID"|i18n("extension/eztags/tags/view")}</th>
								<th>{"Name"|i18n("extension/eztags/tags/view")}</th>
								<th>{"Modified"|i18n("extension/eztags/tags/view")}</th>
							</tr>
							{foreach $nodes as $node}
								<tr>
									<td>{$node.contentobject_id}</td>
									<td><a href={$node.url_alias|ezurl}>{$node.object.name|wash(xhtml)}</a></td>
									<td>{$node.object.modified|datetime('custom', '%d.%m.%Y %H:%i')}</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				{else}
					{"No content"|i18n("extension/eztags/tags/view")}
				{/if}
			</div>

			<div class="right">
				{if $tag.main_tag_id|eq(0)}
					<h2>{'Synonyms'|i18n( 'extension/eztags/tags/view' )}</h2>

					{if $tag.synonyms|count}
						<table class="list" cellpadding="0" border="0">
							<tbody>
								<tr>
									<th class="tight">&nbsp;</th>
									<th>{"ID"|i18n("extension/eztags/tags/view")}</th>
									<th>{"Name"|i18n("extension/eztags/tags/view")}</th>
									<th>{"Modified"|i18n("extension/eztags/tags/view")}</th>
								</tr>
								{foreach $tag.synonyms as $synonym}
									<tr>
										<td><img class="transparent-png-icon" src={concat('tag_icons/small/', $synonym.icon)|ezimage} alt="{$synonym.keyword|wash(xhtml)}" /></td>
										<td>{$synonym.id}</td>
										<td><a href={concat('tags/id/', $synonym.id)|ezurl}>{$synonym.keyword|wash(xhtml)}</a></td>
										<td>{$synonym.modified|datetime('custom', '%d.%m.%Y %H:%i')}</td>
									</tr>
								{/foreach}
							</tbody>
						</table>
					{else}
						{"No synonyms"|i18n("extension/eztags/tags/view")}
					{/if}
				{/if}
			</div>

			<div class="left">
				{if $tag.main_tag_id|eq(0)}
					<h2>{'Subtree limitations'|i18n( 'extension/eztags/tags/view' )}</h2>

					{if $tag.subtree_limitations_count|gt(0)}
						<table class="list" cellpadding="0" border="0">
							<tbody>
								<tr>
									<th class="tight">&nbsp;</th>
									<th>{"Class ID"|i18n("extension/eztags/tags/view")}</th>
									<th>{"Class name"|i18n("extension/eztags/tags/view")}</th>
									<th>{"Attribute identifier"|i18n("extension/eztags/tags/view")}</th>
								</tr>
								{def $c = ''}
								{foreach $tag.subtree_limitations as $l}
									{set $c = fetch(content, class, hash(class_id, $l.contentclass_id))}
									<tr>
										<td>{$c.identifier|class_icon( 'small', $c.name|wash )}</td>
										<td>{$l.contentclass_id}</td>
										<td><a href={concat('class/view/', $l.contentclass_id)|ezurl}>{$c.name|wash(xhtml)}</a></td>
										<td>{$l.identifier}</td>
									</tr>
								{/foreach}
							</tbody>
						</table>
					{else}
						{"No subtree limitations"|i18n("extension/eztags/tags/view")}
					{/if}
				{/if}
			</div>
			<div class="float-break"></div>
		</div>
	</div>
</div>

{undef}