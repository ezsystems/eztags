<div class="tags-view">
    <div class="border-box">
    <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
    <div class="border-ml"><div class="border-mr"><div class="border-mc float-break">
		<div class="tag-header">
			<h1>
				<img class="transparent-png-icon" src={concat('tag_icons/normal/', $tag.icon)|ezimage} title="{$tag.keyword|wash(xhtml)}" alt="{$tag.keyword|wash(xhtml)}" />
				{$tag.keyword|wash(xhtml)}
			</h1>
		</div>
	    <div class="columns-blog float-break">
	        <div class="main-column-position">
	            <div class="main-column float-break">

					{def $nodes_boosted=fetch( 'ezfind', 'search',
					              hash( 'filter', concat('article/tags:"',$tag.keyword,'"'), 'limit', 6, 'boost_functions', hash( 'functions', array( 'ord(meta_published_dt)^2','ord(attr_comments_si)^2' ) )))}

					{if $nodes_boosted['SearchResult']|count}
						<div class="block">
							<h2>Boosted content</h2>
							{foreach $nodes_boosted['SearchResult'] as $node}
								{node_view_gui content_node=$node view=line}<br />
							{/foreach}
						</div>
					{/if}

	            </div>
	        </div>

	        <div class="extrainfo-column-position">
	            <div class="extrainfo-column">

					{def $nodes_latest=fetch( 'tags', 'node_list',
					                   hash( 'alphabet', $tag.keyword, 'classid', array(16), 'limit', 5, 'sort_by', array('published', false()) ) )}

					{if $nodes_latest|count}
						<div class="block">
							<h2>Latest content</h2>
	
							<ul>
								{foreach $nodes_latest as $node}
									<li>{node_view_gui content_node=$node view=listitem}</li>
								{/foreach}
							</ul>
						</div>
					{/if}

					{def $nodes_related=fetch( 'ezfind', 'search',
					              hash( 'limit',0, 'filter', concat('article/tags:"',$tag.keyword,'"'), 'facet', array(hash('field', 'article/tags', 'limit', 6)) ) )}

					{if $nodes_related.SearchExtras.facet_fields.0.nameList|gt(1)}
						<div class="block">
							<h2>Related tags</h2>
							<ul>
								{foreach $nodes_related.SearchExtras.facet_fields.0.nameList as $name sequence $nodes_related.SearchExtras.facet_fields.0.countList as $count}
									{if $name|downcase|ne($tag.keyword|downcase)}
										{def $t=fetch( 'tags', 'object', hash( 'keyword', $name ) )}
										{if is_set($t)}
										{def $url=urlencode($t.keyword) $p=$t}		
										{while $p.parent_id|gt(0)}
											{set $p=$p.parent}
											{set $url=concat(urlencode($p.keyword),"/",$url)}
										{/while}
										<li><img class="transparent-png-icon" src={concat('tag_icons/small/', $t.icon)|ezimage} title="{$t.keyword|wash(xhtml)}" alt="{$t.keyword|wash(xhtml)}" /> <a href={concat("tags/view/",$url)|ezurl}>{$t.keyword}</a> ({$count})</li>
										{/if}
										{undef $t $p $url}
									{/if}
								{/foreach}
							</ul>
						</div>
					{/if}
	            </div>
	        </div>
	    </div>
	</div></div></div>
	<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
	</div>
</div>