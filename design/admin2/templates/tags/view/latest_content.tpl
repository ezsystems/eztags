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