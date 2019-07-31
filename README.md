# eZ Tags
eZ Tags is an eZ Publish extension for taxonomy management and easier classification of content objects, providing more functionality for tagging content objects than ezkeyword datatype included in eZ Publish kernel.

## License, installation instructions and changelog
[License](/LICENSE)

[Installation instructions](/doc/INSTALL.md)

[Changelog](/doc/CHANGELOG.md)

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

You can find the project page on [projects.ez.no](http://projects.ez.no/eztags) and more information on the extension on [Netgen blog](https://netgen.io/tags/view/Netgen%20Tags).

## eZ Publish 5 and eZ Platform support

eZ Tags is also rewritten to eZ Publish 5 and eZ Platform in the form of [Netgen Tags Bundle](https://github.com/netgen/TagsBundle) 

## Other extensions using eZ Tags

1. eZ Smart Tags: http://ez.no/Products/Partner-Solutions/Explore-the-eZ-Market/eZ-Smart-Tags
2. eZ Tag Feed: http://projects.ez.no/eztagfeed
3. eZ Tag Maps: http://projects.ez.no/eztagmaps
