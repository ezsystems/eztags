{def $latest_tags = latest_tags()}
<h2>{'Latest tags'|i18n( 'extension/eztags/tags/dashboard' )}</h2>
<table class="list" cellpadding="0" border="0">
	<tbody>
		<tr>
			<th class="tight">&nbsp;</th>
			<th>{"ID"|i18n("extension/eztags/tags/dashboard")}</th>
			<th>{"Tag name"|i18n("extension/eztags/tags/dashboard")}</th>
			<th>{"Parent tag name"|i18n("extension/eztags/tags/dashboard")}</th>
			<th>{"Modified"|i18n("extension/eztags/tags/dashboard")}</th>
		</tr>
		{foreach $latest_tags as $tag sequence array('bglight', 'bgdark') as $sequence}
			<tr>
				<td><img class="transparent-png-icon" src={concat('tag_icons/small/', $tag.icon)|ezimage} alt="{$tag.keyword|wash(xhtml)}" /></td>
				<td>{$tag.id}</td>
				<td><a href={concat('tags/id/', $tag.id)|ezurl}>{$tag.keyword|wash(xhtml)}</a></td>
				{if $tag.parent}
					<td><a href={concat('tags/id/', $tag.parent.id)|ezurl}>{$tag.parent.keyword|wash(xhtml)}</a></td>
				{else}
					<td>{"No parent"|i18n("extension/eztags/tags/dashboard")}</td>
				{/if}
				<td>{$tag.modified|datetime('custom', '%d.%m.%Y %H:%i')}</td>
			</tr>
		{/foreach}
	</tbody>
</table>