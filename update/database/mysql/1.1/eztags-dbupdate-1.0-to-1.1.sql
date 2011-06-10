ALTER TABLE `eztags`
ADD COLUMN `depth` int(11) NOT NULL default '1' AFTER `keyword`;
