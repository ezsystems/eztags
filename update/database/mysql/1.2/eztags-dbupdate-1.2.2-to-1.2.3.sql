ALTER TABLE `eztags` ADD COLUMN `hidden` int(1) NOT NULL DEFAULT '0' AFTER `remote_id`;
ALTER TABLE `eztags` ADD KEY `eztags_hidden` (`hidden`);
