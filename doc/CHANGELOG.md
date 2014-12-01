# eZ Tags extension changelog

## 2.0

* Multilanguage support for tags!
* Whole core is updated to work with multilingual tags. That means fetches, attribute filter, modules... Everything!
* New administration interface! Specially created to behave & look exactly like the rest of admin interface of eZ Publish
* eZ Tags cloud over Solr! You can now use Solr to create a cloud of your tags
* New attribute, `related_objects_count` is available in eZ Tags attribute content in templates
* New `tag_icon` template operator available to simplify fetching of tag icons
* tags/tag fetch now supports fetching an array of tags, just transfer the array of IDs to tag_id parameter
* Implemented tags/tag_by_url fetch to fetch the tag by its URL (e.g. 'eZ Publish/Extensions/eZ Tags')
* Added a dedicated Solr indexing handler that indexes much more data from eZ Tags attributes
* Added a second extended attribute filter that filters objects that have ALL (as oposed to ANY in the original extended filter) of the specified tags
* Added a third extended attribute filter that filters objects that have ALL of the specified tag groups, matching ANY tag within a tag group (thanks Peter Keung)
* Add inline custom tag for linking to a tag in ezxml datatype
* New translations
* Various bug fixes and optimizations

## 1.4.0

* Add tags reordering support in attribute edit interface (thanks to Thiago Campos Viana)
* Various bug fixes and enhancements

## 1.3.0 (12.05.2014)

* Add user docs compiled from blog posts on http://www.netgenlabs.com/Blog
* Added usage docs
* Added `related_objects_count` attribute for tag object
* Added ability to fetch tags based on their remote ID (thanks Maury Mathieu)
* Disabled `[Icons]/IconMap` by default in `eztags.ini`
* Updated translations (Polish, French, German)
* Call event filters when adding synonymas and tags from content (thanks Benjamin Choquet)
* Call event filters when editing and deleting synonyms (thanks Benjamin Choquet)
* `eztags` attribute view template now sets keywords persistent variable (thanks Patrick Allaert)
* Added ability for `fetchByParentId` and `getChildren()` to support `$offset` and `$limit` for large datasets (thanks David Sayre)
* Various bug fixes and enhancements

## 1.2.2 (12.01.2012)

* Fix regression in ezpEvent hooks that could cause fatal errors in certain situations
* Remote ID support for eZ Tags (thanks [Benjamin Choquet](/bchoquet-heliopsis))
* Option to replace tag synonyms with main tag IDs when indexing (thanks [Benjamin Choquet](/bchoquet-heliopsis))
* Wildcard searching in tags/tags_by_keyword fetch (thanks [Sander van den Akker](/svda))
* Various bug fixes

## 1.2.1 (22.12.2011)

* Updated ezpEvent hooks to be compatible with Smart Tags extension

## 1.2 (07.09.2011)

* Added script that can convert ezkeyword attributes to eztags attributes
* Added support for class attribute serialization (thanks [Heliopsis](/heliopsis))
* eztags datatype content now contains tags attribute to easily fetch all tags added to object attribute
* fromString method in datatype is now more robust, due to certain conditions that could lead to deleting all existing tags from attribute when importing
* Minor cosmetic changes in tags/id view in admin interface
* tags/id view in admin interface now also contains latest tags table, just as tags/dashboard
* tags/id in admin interface now contains list of all child tags at the bottom of the page
* Transferred "hide root tag" option from INI setting to class attribute setting
* eztags datatype can now be configured to set the max number of tags that can be added to the attribute
* Added paging support in tags/view view
* Attribute filter now supports tag_id parameter as array of integers
* Few internal optimisations when editing and displaying tags
* Couple of small bug fixes (thanks [Heliopsis](/heliopsis))

## 1.1 (13.06.2011)

* New fetches (tags/list, tags/list_count, tags/tree, tags/tree_count)
* Added depth database column to eztags table to support the new fetches
* Renamed two existing fetches to better suit the naming scheme. IMPORTANT: This is a breaking change, see [doc/bc/1.1/changes-1.1.txt](/ezsystems/eztags/tree/multilanguage/doc/bc/1.1/changes-1.1.txt) for more details!
* tags/object_by_keyword, along with the new name, now returns all fetched tags. IMPORTANT: This is a breaking change, see [doc/bc/1.1/changes-1.1.txt](/ezsystems/eztags/tree/multilanguage/doc/bc/1.1/changes-1.1.txt) for more details!

## 1.0.1 (18.04.2011)

* Bug fix: Tag icons in tree menu did not work well with the override system (thanks [Jérôme Vieilledent](/lolautruche))

## 1.0 (04.04.2011)

* Initial release
