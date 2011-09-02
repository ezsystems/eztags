#!/usr/bin/env php
<?php

require 'autoload.php';

$cli = eZCLI::instance();

$script = eZScript::instance( array( 'description'    => ( "Converts ezkeyword datatype content to eztags datatype content.\n" .
                                                           "Since the script would require as many publish operations as there are translations\n" .
                                                           "per each object, the script will not republish the objects, but rather update\n" .
                                                           "the current version of currently published objects. Because of that, you will\n" .
                                                           "need to take care of clearing relevant caches, reindexing and so on.\n" .
                                                           "php extension/eztags/bin/php/convertezkeyword.php --from-attr-id=123 --to-attr-id=456 --parent-tag-id=42" ),
                                     'use-session'    => true,
                                     'use-modules'    => false,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( "[from-attr-id:][to-attr-id:][parent-tag-id:]",
                                "",
                                array(
                                    'from-attr-id'  => ( "Specifies source class attribute ID.\n" .
                                                         "Must be of ezkeyword type" ),
                                    'to-attr-id'    => ( "Specifies destination class attribute ID.\n" .
                                                         "Must be of eztags type and located in the same class as from-attr-id" ),
                                    'parent-tag-id' => ( "Specifies where in tags tree will new tags be located.\n" .
                                                         "Cannot be a synonym." )
                                ) );
$script->initialize();

if ( !isset( $options['from-attr-id'] ) || !isset( $options['to-attr-id'] ) || !isset( $options['parent-tag-id'] ) )
{
    $cli->output( "Some of the required parameters are missing.\n" .
                  "Please run the script with --help option to see how to run the script properly." );
    $script->shutdown( 1 );
}

$sourceClassAttributeID = (int) $options['from-attr-id'];
$destClassAttributeID = (int) $options['to-attr-id'];
$parentTagID = (int) $options['parent-tag-id'];

$sourceClassAttribute = eZContentClassAttribute::fetch( $sourceClassAttributeID );
$destClassAttribute = eZContentClassAttribute::fetch( $destClassAttributeID );
$parentTag = eZTagsObject::fetch( $parentTagID );

if ( !$sourceClassAttribute instanceof eZContentClassAttribute || $sourceClassAttribute->attribute( 'data_type_string' ) != 'ezkeyword' )
{
    $cli->output( "Invalid source class attribute." );
    $script->shutdown( 1 );
}

if ( !$destClassAttribute instanceof eZContentClassAttribute || $destClassAttribute->attribute( 'data_type_string' ) != 'eztags'
     || $sourceClassAttribute->attribute( 'contentclass_id' ) != $destClassAttribute->attribute( 'contentclass_id' ) )
{
    $cli->output( "Invalid destination class attribute." );
    $script->shutdown( 1 );
}

if ( !$parentTag instanceof eZTagsObject || (int) $parentTag->attribute( 'main_tag_id' ) != 0 )
{
    $cli->output( "Invalid parent tag." );
    $script->shutdown( 1 );
}

$cli->warning( "This script will NOT republish objects, but rather update the CURRENT" );
$cli->warning( "version of published objects. If you do not wish to do that, you have" );
$cli->warning( "15 seconds to cancel the script! (press Ctrl-C)\n" );
sleep( 15 );

$sourceClassAttributeIdentifier = $sourceClassAttribute->attribute( 'identifier' );
$destClassAttributeIdentifier = $destClassAttribute->attribute( 'identifier' );
$isDestClassAttributeTranslatable = (bool) $destClassAttribute->attribute( 'can_translate' );

$adminUser = eZUser::fetchByName( 'admin' );
$adminUser->loginCurrent();

$db = eZDB::instance();

$offset = 0;
$limit = 50;

$objectCount = eZPersistentObject::count( eZContentObject::definition(), array(
                                              'contentclass_id' => $sourceClassAttribute->attribute( 'contentclass_id' ),
                                              'status' => eZContentObject::STATUS_PUBLISHED
                                          ) );

while ( $offset < $objectCount )
{
    $objects = eZContentObject::fetchFilteredList( array(
                                                       'contentclass_id' => $sourceClassAttribute->attribute( 'contentclass_id' ),
                                                       'status' => eZContentObject::STATUS_PUBLISHED
                                                   ), $offset, $limit );

    foreach ( $objects as $object )
    {
        foreach ( $object->availableLanguages() as $languageCode )
        {
            $object->fetchDataMap( false, $languageCode );
        }

        if ( isset( $object->DataMap[$object->attribute( 'current_version' )] ) )
        {
            $db->begin();

            $languageDataMap = $object->DataMap[$object->attribute( 'current_version' )];
            $initialLanguageCode = $object->initialLanguageCode();

            // first convert the initial (main) language
            $objectAttributes = $languageDataMap[$initialLanguageCode];
            if ( isset( $objectAttributes[$sourceClassAttributeIdentifier] ) && isset( $objectAttributes[$destClassAttributeIdentifier] ) )
            {
                $sourceObjectAttribute = $objectAttributes[$sourceClassAttributeIdentifier];
                $destObjectAttribute = $objectAttributes[$destClassAttributeIdentifier];

                if ( $sourceObjectAttribute->hasContent() )
                {
                    $keywordArray = $sourceObjectAttribute->content()->keywordArray();
                    $tagsArray = array_merge( array_fill( 0, count( $keywordArray ), '0' ), $keywordArray, array_fill( 0, count( $keywordArray ), $parentTagID ) );

                    $destObjectAttribute->fromString( implode( '|#', $tagsArray ) );
                    $destObjectAttribute->store();
                }

                unset( $sourceObjectAttribute );
                unset( $destObjectAttribute );
            }

            unset( $objectAttributes );

            // then all of the rest
            foreach ( $languageDataMap as $languageCode => $objectAttributes )
            {
                if ( $languageCode != $initialLanguageCode && isset( $objectAttributes[$sourceClassAttributeIdentifier] ) && isset( $objectAttributes[$destClassAttributeIdentifier] ) )
                {
                    $sourceObjectAttribute = $objectAttributes[$sourceClassAttributeIdentifier];
                    $destObjectAttribute = $objectAttributes[$destClassAttributeIdentifier];

                    if ( $isDestClassAttributeTranslatable )
                    {
                        if ( $sourceObjectAttribute->hasContent() )
                        {
                            $keywordArray = $sourceObjectAttribute->content()->keywordArray();
                            $tagsArray = array_merge( array_fill( 0, count( $keywordArray ), '0' ), $keywordArray, array_fill( 0, count( $keywordArray ), $parentTagID ) );

                            $destObjectAttribute->fromString( implode( '|#', $tagsArray ) );
                            $destObjectAttribute->store();
                        }
                    }
                    else
                    {
                        $destObjectAttribute->fromString( $languageDataMap[$initialLanguageCode][$destClassAttributeIdentifier]->toString() );
                        $destObjectAttribute->store();
                    }

                    unset( $sourceObjectAttribute );
                    unset( $destObjectAttribute );
                }
            }

            $db->commit();

            unset( $languageDataMap );
            $cli->output( "Converted object ID " . $object->attribute( 'id' ) );
        }
    }

    unset( $objects );
    $offset += $limit;
}

$cli->output( "Done!\n" );
$cli->warning( "For changes to take effect, please clear the caches, reindex your content and so on.\n" );

eZUser::logoutCurrent();
$script->shutdown( 0 );

?>
