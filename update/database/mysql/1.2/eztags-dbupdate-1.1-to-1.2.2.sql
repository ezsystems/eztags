ALTER TABLE `eztags`
ADD COLUMN `remote_id` varchar(100) NOT NULL default '' AFTER `modified`;

UPDATE `eztags` SET `remote_id` = MD5( `id` );

ALTER TABLE `eztags` ADD UNIQUE INDEX `eztags_remote_id` ( `remote_id` );
