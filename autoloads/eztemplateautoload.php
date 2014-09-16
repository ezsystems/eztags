<?php

/**
 * Look in the operator files for documentation on use and parameters definition.
 *
 * @var array $eZTemplateOperatorArray
 */
$eZTemplateOperatorArray = array();

$eZTemplateOperatorArray[] = array( 'script'         => 'extension/eztags/autoloads/eztagstemplatefunctions.php',
                                    'class'          => 'eZTagsTemplateFunctions',
                                    'operator_names' => array( 'eztags_parent_string', 'latest_tags', 'user_limitations' ) );

$eZTemplateOperatorArray[] = array( 'script'         => 'extension/eztags/autoloads/eztagscloud.php',
                                    'class'          => 'eZTagsCloud',
                                    'operator_names' => array( 'eztagscloud' ) );

?>
