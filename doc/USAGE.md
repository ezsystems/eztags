# eZ Tags template usage examples

## Template operators

### eztagscloud

eZ Tags tag cloud template operator has the same parameters as `eztagcloud` template operator shipped with `ezwebin` and `ezdemo` extensions.

The parameters are as follows:

* `class_identifier`: Limits the tag cloud only to tags attached to the objects of specified class
* `parent_node_id`: Limits the tags cloud only to objects located under the specified node ID
* `offset`: Start position for fetching tags
* `limit`: Limits the number of fetched tags
* `sort_by`: Array that specifies the desired sorting and ordering of tags. Possible values for sorting are `keyword` and `count`, and for ordering `true()` (ascending) and `false()` (descending)

The following example lists all the parameters you can use with the operator:

```
{eztagscloud(
    hash(
        'class_identifier', 'article',
        'parent_node_id', 2,
        'offset', 0,
        'limit', 20,
        'sort_by', array(
            'keyword', true()
        )
    )
)}
```

## Template fetch functions

eZ Tags comes bundled with custom fetch functions used to fetch tags in various ways.

### tags/tag fetch function

This fetch function can be used to fetch a single tag:

```
{def $tag = fetch( tags, tag, hash( 'tag_id', 42 ) )}
```

### tags/tags_by_keyword

This fetch function returns all tags with specified keyword:

```
{def $tags = fetch( tags, tags_by_keyword, hash( 'keyword', 'eZ Publish' ) )}
```

### tags/tag_by_remote_id

This fetch function returns the tag with specified remote ID:

```
{def $tag = fetch( tags, tag_by_remote_id, hash( 'remote_id', '1143ae02e8c0995ccd15a1847e886328' ) )}
```

### tags/latest_tags

This fetch function returns the latest tags added to eZ Publish:

You can use optional `parent_tag_id` paremeter to limit the fetch to a certain tag subtree, or `limit` parameter to limit the number of returned tags:

```
{def $latest_tags = fetch(
    tags, tag_by_remote_id,
    hash(
        'parent_tag_id', 42,
        'limit', 10
    )
)}
```

### tags/list, tags/tree

These fetch functions can be used to fetch a list of tags under a specified tag. There is only one difference between `list` and `tree` fetches. While `list` fetch returns tags only on first level below the specified tag, `tree` fetch returns tags from the whole subtree.

The parameters of these fetches are as follows (only `parent_node_id` is required):

* `parent_node_id`: Returns the tags only below the specified tag
* `sort_by`: Array that specifies the desired sorting and ordering of tags. Possible values for sorting are `id`, `parent_id`, `main_tag_id`, `keyword`, `depth`, `path_string`, `modified` and `remote_id` and for ordering `true()` (ascending) and `false()` (descending)
* `offset`: Start position for fetching tags
* `limit`: Limits the number of fetched tags
* `depth`: Defines how many levels below the parent tag will the tags be fetched from. Applicable only to `tree` fetch
* `depth_operator`: Defines the operator that will be used to match the depth level of tags. Applicable only to `tree` fetch. Possible values are:
  1. `lt` (fetches only tags that have the depth lower than specified with `depth` paremeter)
  2. `gt` (fetches only tags that have the depth greater than specified with `depth` paremeter)
  3. `le` (same as `lt`, but also includes tags that have the exact depth specified with `depth parameter`)
  4. `ge` (same as `gt`, but also includes tags that have the exact depth specified with `depth parameter`)
  5. `eq` (fetches only tags that have the depth exactly as defined with `depth` parameter)
* `include_synonyms`: Includes tag synonyms in fetch result. Defaults to false.

The following example shows the `tree` fetch with all the parameters included:

```
{def $tags = fetch(
    tags, tree,
    hash(
        'parent_tag_id', 42,
        'sort_by', array( 'modified', false() ),
        'offset', 0,
        'limit', 15,
        'depth', 4,
        'depth_operator', 'le',
        'include_synonyms', true()
    )
)}
```

### tags/list_count, tags/tree_count

These fetch functions can be used to fetch the count of tags under a specified tag. There is only one difference between `list_count` and `tree_count` fetches. While `list_count` fetch returns the count of tags only on first level below the specified tag, `tree_count` fetch returns the count of tags from the whole subtree.

The parameters of these fetches are `parent_node_id`, `depth`, `depth_operator` and `include_synonyms`. Only `parent_node_id` is required and they all have the same meaning and possible values as matching parameters in `tags/list` and `tags/tree` fetches.

The following example shows the `tree_count` fetch with all the parameters included:

```
{def $tags_count = fetch(
    tags, tree_count,
    hash(
        'parent_tag_id', 42,
        'depth', 4,
        'depth_operator', 'le',
        'include_synonyms', true()
    )
)}
```

## Extended attribute filter

Since `eztags` datatype does not support sorting and filtering in attribute filters in `content/list` and `content/tree` fetches, an extended attribute filter is implemented to allow you to fetch the content which has specified tags attached to it.

The parameters of the extended attribute filter are:

* `tag_id`: integer or array of integers. Defines that the content needs to have at least one of the specified tags attached to it
* `include_synonyms`: Specifies will the synonyms of the specified tags be taken into consideration. Defaults to false

Example usage:

```
{def $nodes = fetch(
    content, list,
    hash(
        'parent_node_id', 2
        'extended_attribute_filter', hash(
            'id', 'TagsAttributeFilter',
            'params', hash(
                'tag_id', array( 42, 24 ),
                'include_synonyms', true()
            )
        )
    )
)}
```

This example returns all content directly below the node with ID = 2 that has either tag with ID = 42, or tag with ID = 24 or one of their synonyms attached to it.
