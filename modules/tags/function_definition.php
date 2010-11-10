<?php

$FunctionList = array();
$FunctionList['object'] = array( 'name' => 'object',
                                        'operation_types' => array( 'read' ),
                                        'call_method' => array( 'class' => 'eZTagsFunctionCollection',
                                                                'method' => 'fetchTagObject' ),
                                        'parameter_type' => 'standard',
                                        'parameters' => array( array( 'name' => 'keyword',
                                                                      'type' => 'string',
                                                                      'required' => true ) ) );
?>
