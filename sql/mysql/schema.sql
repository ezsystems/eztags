CREATE TABLE `eztags` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) NOT NULL default '0',
  `main_tag_id` int(11) NOT NULL default '0',
  `keyword` varchar(255) NOT NULL default '',
  `path_string` varchar(255) NOT NULL default '',
  `modified` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `eztags_keyword` (`keyword`),
  KEY `eztags_keyword_id` (`keyword`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `eztags_attribute_link` (
  `id` int(11) NOT NULL auto_increment,
  `keyword_id` int(11) NOT NULL default '0',
  `objectattribute_id` int(11) NOT NULL default '0',
  `object_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `eztags_attr_link_keyword_id` (`keyword_id`),
  KEY `eztags_attr_link_kid_oaid` (`keyword_id`,`objectattribute_id`),
  KEY `eztags_attr_link_kid_oid` (`keyword_id`,`object_id`),
  KEY `eztags_attr_link_oaid` (`objectattribute_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
