{def
    $old_tags = array()
    $new_tags = array()
}

{foreach $diff.old_content.content.tags as $tag}
    {set $old_tags = $old_tags|append( $tag.keyword )}
{/foreach}

{foreach $diff.new_content.content.tags as $tag}
    {set $new_tags = $new_tags|append( $tag.keyword )}
{/foreach}

{def $all_tags = $new_tags|merge( $old_tags )|unique()}

{def $all_tags_labeled = array()}
{foreach $all_tags as $tag}
    {if and( $new_tags|contains( $tag ), not( $old_tags|contains( $tag ) ) )}
        {set $all_tags_labeled = $all_tags_labeled|append( concat( '<ins>', $tag, '</ins>' ) )}
    {elseif not( $new_tags|contains( $tag ) )}
        {set $all_tags_labeled = $all_tags_labeled|append( concat( '<del>', $tag, '</del>' ) )}
    {else}
        {set $all_tags_labeled = $all_tags_labeled|append( $tag )}
    {/if}
{/foreach}

{$all_tags_labeled|implode( ', ' )}
