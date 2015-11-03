{def
    $old_tags = $diff.old_content.content.keyword_string|explode( '|#' )
    $new_tags = $diff.new_content.content.keyword_string|explode( '|#' )
    $all_tags = $new_tags|merge( $old_tags )|unique()
}

{foreach $all_tags as $tag}
{if and( $new_tags|contains( $tag ), not( $old_tags|contains( $tag ) ) )}
<ins>{$tag}</ins>
{elseif not( $new_tags|contains( $tag ) )}
<del>{$tag}</del>
{else}
{$tag}
{/if}
{delimiter}, {/delimiter}
{/foreach}