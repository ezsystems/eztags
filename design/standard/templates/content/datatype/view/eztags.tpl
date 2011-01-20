{if $attribute.content.keywords|count}
	<p>{'Tags'|i18n('extension/eztags/datatypes')}:
	{foreach $attribute.content.keywords as $keyword}
		{def $t=fetch( 'tags', 'object_by_keyword', hash( 'keyword', $keyword ) )}
		{def $url=urlencode($t.keyword) $p=$t}		
		{while $p.parent_id|gt(0)}
			{set $p=$p.parent}
			{set $url=concat(urlencode($p.keyword),"/",$url)}
		{/while}

		<img class="transparent-png-icon" src={concat('tag_icons/small/', $t.icon)|ezimage} title="{$t.keyword|wash(xhtml)}" /> <a href={concat('/tags/view/', $url)|ezurl}>{$keyword|wash(xhtml)}</a>{delimiter}, {/delimiter}
		{undef $t $p $url}
	{/foreach}</p>
{/if}