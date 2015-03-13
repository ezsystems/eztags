CREATE SEQUENCE eztags_s;
CREATE TABLE eztags (
   id integer DEFAULT nextval('eztags_s'::text) NOT NULL,
   parent_id integer not null default 0,
   main_tag_id integer not null default 0,
   keyword varchar(255) NOT NULL default '',
   depth integer NOT NULL default 1,
   path_string varchar(255) NOT NULL default '',
   modified integer NOT NULL default 0,
   remote_id varchar(100) NOT NULL default '',
   PRIMARY KEY (id),
   CONSTRAINT remote_id UNIQUE  (remote_id)
);
CREATE INDEX eztags_keyword ON eztags (
   keyword
);
CREATE INDEX eztags_keyword_id ON eztags (
   keyword,
   id
);
CREATE SEQUENCE eztags_attribute_link_s;
CREATE TABLE eztags_attribute_link (
   id integer DEFAULT nextval('eztags_attribute_link_s'::text) NOT NULL,
   keyword_id integer not null default 0,
   objectattribute_id integer not null default 0,
   objectattribute_version integer not null default 0,
   object_id integer not null default 0,
   PRIMARY KEY (id)
);
CREATE INDEX eztags_attr_link_keyword_id ON eztags_attribute_link (
   keyword_id
);
CREATE INDEX eztags_attr_link_kid_oaid_oav ON eztags_attribute_link (
   keyword_id,
   objectattribute_id,
   objectattribute_version
);
CREATE INDEX eztags_attr_link_kid_oid ON eztags_attribute_link (
   keyword_id,
   object_id
);
CREATE INDEX eztags_attr_link_oaid_oav ON eztags_attribute_link (
   objectattribute_id,
   objectattribute_version
); 