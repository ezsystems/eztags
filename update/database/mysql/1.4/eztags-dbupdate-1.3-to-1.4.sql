ALTER TABLE `eztags_attribute_link`
ADD COLUMN `priority` int(11) NOT NULL default 0 AFTER `object_id`;
