Changes to BC and behavior in version 1.1
=========================================

Renamed fetches
---------------

`tags/object` fetch has been renamed to `tags/tag`. `tags/object_by_keyword` fetch has been renamed to `tags/tags_by_keyword`.

Adding new fetches required old ones to be updated to better suit the naming scheme. This has great impact on all templates (built in and custom) that use these fetches. They need to be modified to use updated fetch names.

Change of behavior
------------------

`tags/object_by_keyword` fetch, along with the rename has been modified to return all tags it fetches and not just the first one.

It makes more sense to filter out what you need directly in the templates, then to limit the fetch functionality.

Changes to method signatures
----------------------------

`eZTagsFunctionCollection::fetchTagObject` renamed to `eZTagsFunctionCollection::fetchTag` to match the corresponding fetch.

`eZTagsFunctionCollection::fetchTagObjectByKeyword` renamed to `eZTagsFunctionCollection::fetchTagsByKeyword` to match the corresponding fetch.

Added parameter `$sorts` to the end of the parameter list in `eZTagsObject::fetchList` method, non required, defaults to `null`.
