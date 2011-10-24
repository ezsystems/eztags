#!/usr/bin/env php
<?php

require 'autoload.php';

$cli = eZCLI::instance();

$script = eZScript::instance( array( 'description'    => ( '\nInitializes tag translations for upgrade to eZ Tags 2.0.\n' ),
                                     'use-session'    => false,
                                     'use-modules'    => false,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( '[locale:]', '', array( 'locale' => 'Locale to initialize tag translations with' ) );
$script->initialize();

if ( !isset( $options['locale'] ) )
{
    $cli->error( "Locale parameter is needed by the script but wasn't specified." );
    $script->shutdown( 1 );
}

$language = eZContentLanguage::fetchByLocale( $options['locale'] );
if ( !$language instanceof eZContentLanguage )
{
    $cli->error( "Invalid locale specified." );
    $script->shutdown( 1 );
}

$db = eZDB::instance();
$db->begin();

$languageID = (int) $language->attribute( 'id' );
$locale = $db->escapeString( $language->attribute( 'locale' ) );

$db->query( "UPDATE eztags SET main_language_id = $languageID, language_mask = $languageID + 1" );

$results = $db->arrayQuery( "SELECT id, keyword FROM eztags" );
if ( is_array( $results ) )
{
    foreach ( $results as $result )
    {
        $tagID = (int) $result['id'];
        $keyword = $db->escapeString( $result['keyword'] );
        $db->query( "INSERT INTO eztags_keyword VALUES( $tagID, $languageID + 1, '$keyword', '$locale', 1 )" );
    }
}

$db->commit();
$script->shutdown();

?>
