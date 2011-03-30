ALTER TABLE `eztags`
ADD COLUMN `main_tag_id` int(11) NOT NULL default '0' AFTER `parent_id`;

ALTER TABLE `eztags`
ADD COLUMN `path_string` varchar(255) NOT NULL default '' AFTER `keyword`;

-- START: Versioning support

ALTER TABLE `eztags_attribute_link`
ADD COLUMN `objectattribute_version` int(11) NOT NULL default '0' AFTER `objectattribute_id`;

UPDATE eztags_attribute_link l
INNER JOIN ezcontentobject o ON l.object_id = o.id
SET l.objectattribute_version = o.current_version;

DROP INDEX `eztags_attr_link_kid_oaid` ON `eztags_attribute_link`;
DROP INDEX `eztags_attr_link_oaid` ON `eztags_attribute_link`;

CREATE INDEX `eztags_attr_link_kid_oaid_oav` ON `eztags_attribute_link` ( `keyword_id`, `objectattribute_id`, `objectattribute_version` );
CREATE INDEX `eztags_attr_link_oaid_oav` ON `eztags_attribute_link` ( `objectattribute_id`, `objectattribute_version` );

-- END: Versioning support
