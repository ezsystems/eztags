{* 

Modified version of the standard Google navigator to allow use of a 
custom offset when more than one navigator is used on a page.

e.g. Pagination for the 'Latest content' section in the tag view which 
also paginates child tags.

*}
{def $navigator_offset = $view_parameters.offset}
{if is_set($custom_offset)}
  {set $navigator_offset = $view_parameters.custom_offset}  
{/if}
{default page_uri_suffix=false()
         left_max=7
         right_max=6}
{default name=ViewParameter
         page_uri_suffix=false()
         left_max=$left_max
         right_max=$right_max}

{let page_count=int( ceil( div( $item_count,$item_limit ) ) )
      current_page=min($:page_count,
                       int( ceil( div( first_set( $navigator_offset, 0),
                                       $item_limit ) ) ) )
      item_previous=sub( mul( $:current_page, $item_limit ),
                         $item_limit )
      item_next=sum( mul( $:current_page, $item_limit ),
                     $item_limit )

      left_length=min($ViewParameter:current_page,$:left_max)
      right_length=max(min(sub($ViewParameter:page_count,$ViewParameter:current_page,1),$:right_max),0)
      view_parameter_text=""
      offset_text=eq( ezini( 'ControlSettings', 'AllowUserVariables', 'template.ini' ), 'true' )|choose( '/offset/', '/(offset)/' )}

{if is_set($custom_offset)}
  {set $offset_text=eq( ezini( 'ControlSettings', 'AllowUserVariables', 'template.ini' ), 'true' )|choose( '/custom_offset/', '/(custom_offset)/' )}
{/if}

{* Create view parameter text with the exception of offset/custom_offset *}
{section loop=$view_parameters}
 {section-exclude match=$:key|eq('offset')}
 {section-exclude match=$:key|eq('custom_offset')}
 {section-exclude match=$:item|eq('')}
 {set view_parameter_text=concat($:view_parameter_text,'/(',$:key,')/',$:item)}
{/section}


{section show=$:page_count|gt(1)}

<div class="pagenavigator">
<p>

     {switch match=$:item_previous|lt(0) }
       {case match=0}
      <span class="previous"><a href={concat($page_uri,$:item_previous|gt(0)|choose('',concat($:offset_text,$:item_previous)),$:view_parameter_text,$page_uri_suffix)|ezurl}><span class="text">&laquo;&nbsp;{"Previous"|i18n("design/admin/navigator")}</span></a></span>
       {/case}
       {case}
      <span class="previous"><span class="text disabled">&laquo;&nbsp;{"Previous"|i18n("design/admin/navigator")}</span></span>
       {/case}
     {/switch}

    {switch match=$:item_next|lt($item_count)}
      {case match=1}
        <span class="next"><a href={concat($page_uri,$:offset_text,$:item_next,$:view_parameter_text,$page_uri_suffix)|ezurl}><span class="text">{"Next"|i18n("design/admin/navigator")}&nbsp;&raquo;</span></a></span>
      {/case}
      {case}
        <span class="next"><span class="text disabled">{"Next"|i18n("design/admin/navigator")}&nbsp;&raquo;</span></span>
      {/case}
    {/switch}

<span class="pages">
{if $:current_page|gt($:left_max)}
<a href={concat($page_uri,$:view_parameter_text,$page_uri_suffix)|ezurl}>1</a>
{if sub($:current_page,$:left_length)|gt(1)}
...
{/if}
{/if}

    {section loop=$:left_length}
        {let page_offset=sum(sub($ViewParameter:current_page,$ViewParameter:left_length),$:index)}
          <span class="other"><a href={concat($page_uri,$:page_offset|gt(0)|choose('',concat($:offset_text,mul($:page_offset,$item_limit))),$ViewParameter:view_parameter_text,$page_uri_suffix)|ezurl}>{$:page_offset|inc}</a></span>
        {/let}
    {/section}

        <span class="current">{$:current_page|inc}</span>

    {section loop=$:right_length}
        {let page_offset=sum($ViewParameter:current_page,1,$:index)}
          <span class="other"><a href={concat($page_uri,$:page_offset|gt(0)|choose('',concat($:offset_text,mul($:page_offset,$item_limit))),$ViewParameter:view_parameter_text,$page_uri_suffix)|ezurl}>{$:page_offset|inc}</a></span>
        {/let}
    {/section}

{if $:page_count|gt(sum($:current_page,$:right_max,1))}
{if sum($:current_page,$:right_max,2)|lt($:page_count)}
<span class="other">...</span>
{/if}
<span class="other"><a href={concat($page_uri,$:page_count|dec|gt(0)|choose('',concat($:offset_text,mul($:page_count|dec,$item_limit))),$:view_parameter_text,$page_uri_suffix)|ezurl}>{$:page_count}</a></span>
{/if}

</span>

</p>
<div class="break"></div>
</div>

{/section}

 {/let}
{/default}
{/default}

