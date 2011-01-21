<?php

$FunctionList = array();
$FunctionList['object'] = array( 'name' => 'object',
                                        'operation_types' => array( 'read' ),
                                        'call_method' => array( 'class' => 'eZTagsFunctionCollection',
                                                                'method' => 'fetchTagObject' ),
                                        'parameter_type' => 'standard',
                                        'parameters' => array( array( 'name' => 'tag_id',
                                                                      'type' => 'integer',
                                                                      'required' => true ) ) );
$FunctionList['object_by_keyword'] = array( 'name' => 'object_by_keyword',
                                        'operation_types' => array( 'read' ),
                                        'call_method' => array( 'class' => 'eZTagsFunctionCollection',
                                                                'method' => 'fetchTagObjectByKeyword' ),
                                        'parameter_type' => 'standard',
                                        'parameters' => array( array( 'name' => 'keyword',
                                                                      'type' => 'string',
                                                                      'required' => true ) ) );
?>
