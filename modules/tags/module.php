<?php

$Module = array( 'name'            => 'eZTags',
                 'variable_params' => true );

$ViewList = array();
$ViewList['treemenu'] = array(
    'functions'               => array( 'read' ),
    'script'                  => 'treemenu.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params'                  => array( 'TagID', 'Modified', 'Expiry', 'Perm' ) );

$ViewList['dashboard'] = array(
    'functions'               => array( 'dashboard' ),
    'script'                  => 'dashboard.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params'                  => array(),
    'unordered_params'        => array( 'offset' => 'Offset', 'tab' => 'Tab' ) );

$ViewList['id'] = array(
    'functions'               => array( 'id' ),
    'script'                  => 'id.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params'                  => array( 'TagID', 'Locale' ),
    'unordered_params'        => array( 'offset' => 'Offset', 'tab' => 'Tab' ) );

$ViewList['view'] = array(
    'functions'               => array( 'view' ),
    'script'                  => 'view.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params'                  => array( 'TagName' ),
    'unordered_params'        => array( 'offset' => 'Offset' ) );

$ViewList['add'] = array(
    'functions'               => array( 'add' ),
    'script'                  => 'add.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params'                  => array( 'ParentTagID', 'Locale' ) );

$ViewList['addsynonym'] = array(
    'functions'               => array( 'addsynonym' ),
    'script'                  => 'addsynonym.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params'                  => array( 'MainTagID', 'Locale' ) );

$ViewList['edit'] = array(
    'functions'               => array( 'edit' ),
    'script'                  => 'edit.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params'                  => array( 'TagID', 'Locale' ) );

$ViewList['movetags'] = array(
    'functions'               => array( 'edit' ),
    'script'                  => 'movetags.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params'                  => array() );

$ViewList['translation'] = array(
    'functions'               => array( 'edit' ),
    'script'                  => 'translation.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params'                  => array() );

$ViewList['editsynonym'] = array(
    'functions'               => array( 'editsynonym' ),
    'script'                  => 'editsynonym.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params'                  => array( 'TagID', 'Locale' ) );

$ViewList['delete'] = array(
    'functions'               => array( 'delete' ),
    'script'                  => 'delete.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params'                  => array( 'TagID' ) );

$ViewList['deletetags'] = array(
    'functions'               => array( 'delete' ),
    'script'                  => 'deletetags.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params'                  => array() );

$ViewList['deletesynonym'] = array(
    'functions'               => array( 'deletesynonym' ),
    'script'                  => 'deletesynonym.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params'                  => array( 'TagID' ) );

$ViewList['makesynonym'] = array(
    'functions'               => array( 'makesynonym' ),
    'script'                  => 'makesynonym.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params'                  => array( 'TagID' ) );

$ViewList['merge'] = array(
    'functions'               => array( 'merge' ),
    'script'                  => 'merge.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params'                  => array( 'TagID' ) );

$ViewList['search'] = array(
    'functions'               => array( 'search' ),
    'script'                  => 'search.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params'                  => array(),
    'unordered_params'        => array( 'offset' => 'Offset' ) );

$TagID = array(
    'name'      => 'Tag',
    'values'    => array(),
    'extension' => 'eztags',
    'path'      => 'classes/',
    'file'      => 'eztagsobject.php',
    'class'     => 'eZTagsObject',
    'function'  => 'fetchLimitations',
    'parameter' => array() );

$FunctionList = array();
$FunctionList['read']          = array();
$FunctionList['dashboard']     = array();
$FunctionList['id']            = array();
$FunctionList['view']          = array();
$FunctionList['add']           = array( 'Tag' => $TagID );
$FunctionList['addsynonym']    = array();
$FunctionList['edit']          = array();
$FunctionList['editsynonym']   = array();
$FunctionList['delete']        = array();
$FunctionList['deletesynonym'] = array();
$FunctionList['makesynonym']   = array();
$FunctionList['merge']         = array();
$FunctionList['search']        = array();
