UPDATE `ezcontentclass_attribute`
SET `data_text1` = 'eztags'
WHERE `data_type_string` = 'eztags'
AND `data_int2` = 0;

UPDATE `ezcontentclass_attribute`
SET `data_text1` = 'select'
WHERE `data_type_string` = 'eztags'
AND `data_int2` = 1;
