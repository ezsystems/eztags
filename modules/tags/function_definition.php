<?php

$FunctionList = array();
$FunctionList['object_by_keyword'] = array( 'name' => 'object_by_keyword',
                                        'operation_types' => array( 'read' ),
                                        'call_method' => array( 'class' => 'eZTagsFunctionCollection',
                                                                'method' => 'fetchTagObjectByKeyword' ),
                                        'parameter_type' => 'standard',
                                        'parameters' => array( array( 'name' => 'keyword',
                                                                      'type' => 'string',
                                                                      'required' => true ) ) );
?>
