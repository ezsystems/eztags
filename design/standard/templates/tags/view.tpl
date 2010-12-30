<div class="tags-view extrainfo">
<div class="float-break" style="padding: 0 17em 0 0">
    <div class="main-column-position">
        <div class="main-column float-break">

		    <div class="border-box">
		    <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
		    <div class="border-ml"><div class="border-mr"><div class="border-mc float-break">

			<div class="tag-header">
				<h1>
				<img class="transparent-png-icon" src={concat('tag_icons/normal/', $tag.icon)|ezimage} title="{$tag.keyword|wash(xhtml)}" alt="{$tag.keyword|wash(xhtml)}" />
				{$tag.keyword|wash(xhtml)}
				</h1>
			</div>

			{def $nodes_latest = fetch('content', 'tree', hash('parent_node_id', 2,
														'extended_attribute_filter', hash('id', 'TagsAttributeFilter',
															'params', hash('tag_id', $tag.id, 'include_synonyms', true())),
														'limit', 10,
														'sort_by', array('published', false())))}

			{if $nodes_latest|count}
				<div class="block">
					<h2>{'Latest content'|i18n( 'extension/eztags/tags/view' )}</h2>
					<ul>
						{foreach $nodes_latest as $node}
							<li>{node_view_gui content_node=$node view=listitem}</li>
						{/foreach}
					</ul>
				</div>
			{/if}

			</div></div></div>
			<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
			</div>
        </div>
    </div>

	{if ezini('SearchSettings', 'SearchEngine', 'site.ini')|eq('ezsolr')}
	    <div class="extrainfo-column-position">
	        <div class="extrainfo-column">
	
			    <div class="border-box">
			    <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
			    <div class="border-ml"><div class="border-mr"><div class="border-mc float-break">
	
				{def $nodes_related=fetch( 'ezfind', 'search',
				              hash( 'limit', 0, 'filter', concat('ezf_df_tags:"',$tag.keyword,'"'), 'facet', array(hash('field', 'ezf_df_tags', 'limit', 6)) ) )}
	
				{if $nodes_related.SearchExtras.facet_fields.0.nameList|gt(1)}
					<div class="block">
						<h2>{'Related tags'|i18n( 'extension/eztags/tags/view' )}</h2>
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
									{undef $p $url}
									{/if}
									{undef $t}
								{/if}
							{/foreach}
						</ul>
					</div>
				{/if}
				</div></div></div>
				<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
				</div>
	        </div>
	    </div>
    {/if}
</div>
</div>