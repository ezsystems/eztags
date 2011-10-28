ALTER TABLE `eztags`
ADD COLUMN `main_language_id` int(11) NOT NULL default '0' AFTER `modified`;

ALTER TABLE `eztags`
ADD COLUMN `language_mask` int(11) NOT NULL default '0' AFTER `main_language_id`;

ALTER TABLE `eztags`
ADD COLUMN `remote_id` varchar(100) NOT NULL default '' AFTER `language_mask`;

UPDATE `eztags` SET `remote_id` = MD5( `id` );

ALTER TABLE `eztags` ADD UNIQUE INDEX `eztags_remote_id` ( `remote_id` );

CREATE TABLE `eztags_keyword` (
  `keyword_id` int(11) NOT NULL default '0',
  `language_id` int(11) NOT NULL default '0',
  `keyword` varchar(255) NOT NULL default '',
  `locale` varchar(255) NOT NULL default '',
  `status` int(11) NOT NULL default '0',
  PRIMARY KEY ( `keyword_id`, `locale` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;