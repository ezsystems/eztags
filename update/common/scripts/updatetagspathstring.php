#!/usr/bin/env php
<?php

require 'autoload.php';

$cli = eZCLI::instance();

$script = eZScript::instance( array( 'description'    => ( '\nUpdates path string of all the tags.\n' ),
                                     'use-session'    => false,
                                     'use-modules'    => false,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( '', '', array() );
$script->initialize();

$limit = 20;
$offset = 0;

$db = eZDB::instance();

$script->setIterationData( '.', '~' );

while ( $firstLevelTags = eZTagsObject::fetchList( array( 'parent_id' => 0, 'main_tag_id' => 0 ), array( 'offset' => $offset, 'limit' => $limit ), null, true ) )
{
    foreach ( $firstLevelTags as $tag )
    {
        $tagID = $tag->attribute( 'id' );

        $db->begin();

        $tag->updatePathString();

        $db->commit();

        $script->iterate( $cli, true, 'Updated path string of tag ID = $tagID and all its children.' );
    }

    $offset += $limit;
}

$script->shutdown();
