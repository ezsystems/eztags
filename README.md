# eZ Tags
eZ Tags is an eZ Publish extension for taxonomy management and easier classification of content objects, providing more functionality for tagging content objects than ezkeyword datatype included in eZ Publish kernel.

## License, installation instructions and changelog
[License](/ezsystems/eztags/tree/multilanguage/LICENSE)

[Installation instructions](/ezsystems/eztags/tree/multilanguage/doc/INSTALL.md)

[Changelog](/ezsystems/eztags/tree/multilanguage/doc/CHANGELOG.md)

## About the extension
Main advantages of eZ Tags extension over ezkeyword datatype are:

* tree hierarchy of tags
* easy management through eZ Publish admin interface for adding, deleting and editing tags
* easy tagging in object edit interface with autocomplete, suggestion and in place addition of new tags
* upgraded tags view interface, similar to content view, providing access to $tag variable (current tag viewed)
* extended attribute filter for content list/tree fetch

eZ Tags is not only able to replace the ezkeyword datatype, but can be used for all taxonomies, including:

* closed classifications which are usually predefined
* open classifications like user tags (usually referred to as "folksonomy")
* combination of both

## What to do with it
Here are some examples on what you can do with eZ Tags:

1. replace the ezkeyword datatype. Migration should be straightforward as database schema is very similar. You will get the hierarchy and management which is missing in ezkeyword.
2. replace the closed classification based on ezselection or ezobjectrelation(list) datatype. You will get much easier input interface, easier management & maintenance, better performance
3. be able to change from open to closed and vice versa when ever you need
4. provide a better user experience to your editors
5. create dynamic pages based on tagged content

You can find the project page on [projects.ez.no](http://projects.ez.no/eztags) and more information on the extension on [Netgen blog](http://www.netgenlabs.com/tags/view/ezpublish/extensions/eztags).
