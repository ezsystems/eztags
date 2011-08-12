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
    'unordered_params'        => array( 'offset' => 'Offset' ) );

$ViewList['id'] = array(
    'functions'               => array( 'id' ),
    'script'                  => 'id.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params'                  => array( 'TagID' ),
    'unordered_params'        => array( 'offset' => 'Offset' ) );

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
    'params'                  => array( 'ParentTagID' ) );

$ViewList['addsynonym'] = array(
    'functions'               => array( 'addsynonym' ),
    'script'                  => 'addsynonym.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params'                  => array( 'MainTagID' ) );

$ViewList['edit'] = array(
    'functions'               => array( 'edit' ),
    'script'                  => 'edit.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params'                  => array( 'TagID' ) );

$ViewList['editsynonym'] = array(
    'functions'               => array( 'editsynonym' ),
    'script'                  => 'editsynonym.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params'                  => array( 'TagID' ) );

$ViewList['delete'] = array(
    'functions'               => array( 'delete' ),
    'script'                  => 'delete.php',
    'default_navigation_part' => 'eztagsnavigationpart',
    'params'                  => array( 'TagID' ) );

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

$ClassID = array(
    'name'      => 'Class',
    'values'    => array(),
    'path'      => 'classes/',
    'file'      => 'ezcontentclass.php',
    'class'     => 'eZContentClass',
    'function'  => 'fetchList',
    'parameter' => array( 0, false, false, array( 'name' => 'asc' ) ) );

$TagID = array(
    'name'      => 'Tag',
    'values'    => array(),
    'extension' => 'eztags',
    'path'      => 'classes/',
    'file'      => 'eztagsobject.php',
    'class'     => 'eZTagsObject',
    'function'  => 'fetchList',
    'parameter' => array( array( 'parent_id' => 0, 'main_tag_id' => 0 ), null, false ) );

$FunctionList = array();
$FunctionList['read']          = array( 'Class' => $ClassID );
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

?>
