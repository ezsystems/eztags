ALTER TABLE `eztags`
ADD COLUMN `main_language_id` int(11) NOT NULL default '0' AFTER `remote_id`;

ALTER TABLE `eztags`
ADD COLUMN `language_mask` int(11) NOT NULL default '0' AFTER `main_language_id`;

CREATE TABLE `eztags_keyword` (
  `keyword_id` int(11) NOT NULL default '0',
  `language_id` int(11) NOT NULL default '0',
  `keyword` varchar(255) NOT NULL default '',
  `locale` varchar(255) NOT NULL default '',
  `status` int(11) NOT NULL default '0',
  PRIMARY KEY ( `keyword_id`, `locale` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
