{if $attribute.content.tag_ids|count}
	<p>{'Tags'|i18n('extension/eztags/datatypes')}:
	{foreach $attribute.content.tag_ids as $tag_id}
		{def $t=fetch( 'tags', 'object', hash( 'tag_id', $tag_id ) )}
		{def $url=urlencode($t.keyword) $p=$t}		
		{while $p.parent_id|gt(0)}
			{set $p=$p.parent}
			{set $url=concat(urlencode($p.keyword),"/",$url)}
		{/while}

		<img class="transparent-png-icon" src={concat('tag_icons/small/', $t.icon)|ezimage} title="{$t.keyword|wash(xhtml)}" alt="{$t.keyword|wash(xhtml)}" /> <a href={concat('/tags/view/', $url)|ezurl}>{$t.keyword|wash(xhtml)}</a>{delimiter}, {/delimiter}
		{undef $t $p $url}
	{/foreach}</p>
{/if}