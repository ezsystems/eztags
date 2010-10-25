<?php

$Module = array( 'name' => 'eZTags',
                 'variable_params' => true );

$ViewList = array();
$ViewList['treemenu'] = array(
    'functions' => array( 'read' ),
    'script' => 'treemenu.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params' => array( 'TagID', 'Modified', 'Expiry', 'Perm' ) );

$ViewList['dashboard'] = array(
    'functions' => array( 'dashboard' ),
    'script' => 'dashboard.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params' => array( ),
    'unordered_params' => array( ) );

$ViewList['id'] = array(
    'functions' => array( 'id' ),
    'script' => 'id.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params' => array( 'TagID' ) );

$ViewList['view'] = array(
    'functions' => array( 'view' ),
    'script' => 'view.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params' => array( 'TagName' ) );

$ViewList['add'] = array(
    'functions' => array( 'add' ),
    'script' => 'add.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params' => array( 'ParentTagID' ) );

$ViewList['edit'] = array(
    'functions' => array( 'edit' ),
    'script' => 'edit.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params' => array( 'TagID' ) );

$ViewList['delete'] = array(
    'functions' => array( 'delete' ),
    'script' => 'delete.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params' => array( 'TagID' ) );

$ClassID = array(
    'name'=> 'Class',
    'values'=> array(),
    'path' => 'classes/',
    'file' => 'ezcontentclass.php',
    'class' => 'eZContentClass',
    'function' => 'fetchList',
    'parameter' => array( 0, false, false, array( 'name' => 'asc' ) )
    );

$FunctionList = array();
$FunctionList['read'] = array( 'Class' => $ClassID );
$FunctionList['dashboard'] = array();
$FunctionList['id'] = array();
$FunctionList['view'] = array();
$FunctionList['add'] = array();
$FunctionList['edit'] = array();
$FunctionList['delete'] = array();

?>
