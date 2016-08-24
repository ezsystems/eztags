# eZ Tags extension upgrade instructions

## Upgrade from 2.1 to 2.2

Unpack the downloaded package into the `extension` directory of your eZ Publish installation

Regenerate autoload array by running the following from your eZ Publish root folder

    php bin/php/ezpgenerateautoloads.php --extension

or go to Setup -> Extensions and click the "Regenerate autoload arrays" button

Clear all caches (from admin 'Setup' tab or from command line).




## Upgrade from 2.0 to 2.1

Unpack the downloaded package into the `extension` directory of your eZ Publish installation

Regenerate autoload array by running the following from your eZ Publish root folder

    php bin/php/ezpgenerateautoloads.php --extension

or go to Setup -> Extensions and click the "Regenerate autoload arrays" button

Run SQL upgrade script by using the following command from your eZ Publish root folder,
replacing `user`, `password`, `host` and `database` with correct values and removing double quotes

    mysql -u "user" -p"password" -h"host" "database" < extension/eztags/update/database/mysql/2.1/eztags-dbupdate-2.0-to-2.1.sql

Clear all caches (from admin 'Setup' tab or from command line).




## Upgrade from 1.4 to 2.0

Unpack the downloaded package into the `extension` directory of your eZ Publish installation

Regenerate autoload array by running the following from your eZ Publish root folder

    php bin/php/ezpgenerateautoloads.php --extension

or go to Setup -> Extensions and click the "Regenerate autoload arrays" button

Run SQL upgrade script by using the following command from your eZ Publish root folder,
replacing `user`, `password`, `host` and `database` with correct values and removing double quotes

    mysql -u "user" -p"password" -h"host" "database" < extension/eztags/update/database/mysql/2.0/eztags-dbupdate-1.2.2-to-2.0.sql

Run the PHP upgrade script by using the following command from your eZ Publish root folder:

    php extension/eztags/update/common/scripts/2.0/initializetagtranslations.php --locale=cro-HR

Locale parameter in the above command will be used to set the main language of your existing tags, so make sure
you replace it with the value of your choice.

If you used a modified eZ Find Solr schema for tag suggestions, you must update it:

Starting from the original `schema.xml` that comes with eZ Find, add the following inside `<fields>` element and then restart Tomcat/Jetty:

    <field name="ezf_df_tags" type="lckeyword" indexed="true" stored="true" multiValued="true" termVectors="true"/>
    <field name="ezf_df_tag_ids" type="sint" indexed="true" stored="true" multiValued="true" termVectors="true"/>

Finally, clear all caches (from admin 'Setup' tab or from command line).




## Upgrade from 1.3 to 1.4

Unpack the downloaded package into the `extension` directory of your eZ Publish installation

Regenerate autoload array by running the following from your eZ Publish root folder

    php bin/php/ezpgenerateautoloads.php --extension

or go to Setup -> Extensions and click the "Regenerate autoload arrays" button

Run SQL upgrade script by using the following command from your eZ Publish root folder,
replacing `user`, `password`, `host` and `database` with correct values and removing double quotes

    mysql -u "user" -p"password" -h"host" "database" < extension/eztags/update/database/mysql/1.4/eztags-dbupdate-1.3-to-1.4.sql

Clear all caches (from admin 'Setup' tab or from command line).




## Upgrade from 1.2.2 to 1.3

Unpack the downloaded package into the `extension` directory of your eZ Publish installation

Regenerate autoload array by running the following from your eZ Publish root folder

    php bin/php/ezpgenerateautoloads.php --extension

or go to Setup -> Extensions and click the "Regenerate autoload arrays" button

Clear all caches (from admin 'Setup' tab or from command line).




## Upgrade from 1.2(.1) to 1.2.2

Unpack the downloaded package into the `extension` directory of your eZ Publish installation

Regenerate autoload array by running the following from your eZ Publish root folder

    php bin/php/ezpgenerateautoloads.php --extension

or go to Setup -> Extensions and click the "Regenerate autoload arrays" button

Run SQL upgrade script by using the following command from your eZ Publish root folder,
replacing `user`, `password`, `host` and `database` with correct values and removing double quotes

    mysql -u "user" -p"password" -h"host" "database" < extension/eztags/update/database/mysql/1.2/eztags-dbupdate-1.1-to-1.2.2.sql

Clear all caches (from admin 'Setup' tab or from command line).




## Upgrade from 1.1 to 1.2

Unpack the downloaded package into the `extension` directory of your eZ Publish installation

Regenerate autoload array by running the following from your eZ Publish root folder

    php bin/php/ezpgenerateautoloads.php --extension

or go to Setup -> Extensions and click the "Regenerate autoload arrays" button

Clear all caches (from admin 'Setup' tab or from command line).




## Upgrade from 1.0 to 1.1

Unpack the downloaded package into the `extension` directory of your eZ Publish installation

Regenerate autoload array by running the following from your eZ Publish root folder

    php bin/php/ezpgenerateautoloads.php --extension

or go to Setup -> Extensions and click the "Regenerate autoload arrays" button

Run SQL upgrade script by using the following command from your eZ Publish root folder,
replacing `user`, `password`, `host` and `database` with correct values and removing double quotes

    mysql -u "user" -p"password" -h"host" "database" < extension/eztags/update/database/mysql/1.1/eztags-dbupdate-1.0-to-1.1.sql

Clear all caches (from admin 'Setup' tab or from command line).

Run PHP upgrade script from your eZ Publish root folder:

    php extension/eztags/update/common/scripts/updatetagsdepth.php

Read [doc/bc/1.1/CHANGES-1.1.md](/doc/bc/1.1/CHANGES-1.1.md) as there are some breaking changes introduced with eZ Tags 1.1 and update your templates accordingly




## Upgrade from 1.0beta/1.0alpha to 1.0

Unpack the downloaded package into the `extension` directory of your eZ Publish installation

Regenerate autoload array by running the following from your eZ Publish root folder

    php bin/php/ezpgenerateautoloads.php --extension

or go to Setup -> Extensions and click the "Regenerate autoload arrays" button

Run SQL upgrade script by using the following command from your eZ Publish root folder,
replacing `user`, `password`, `host` and `database` with correct values and removing double quotes

    mysql -u "user" -p"password" -h"host" "database" < extension/eztags/update/database/mysql/1.0/unstable/eztags-dbupdate-1.0beta-to-1.0.sql

Clear all caches (from admin 'Setup' tab or from command line).

Run PHP upgrade script from your eZ Publish root folder:

    php extension/eztags/update/common/scripts/updatetagspathstring.php
