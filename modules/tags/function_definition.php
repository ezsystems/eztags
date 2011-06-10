<?php

$FunctionList = array();

$FunctionList['tag'] = array( 'name'            => 'tag',
                              'operation_types' => array( 'read' ),
                              'call_method'     => array( 'class'  => 'eZTagsFunctionCollection',
                                                          'method' => 'fetchTag' ),
                              'parameter_type'  => 'standard',
                              'parameters'      => array( array( 'name'     => 'tag_id',
                                                                 'type'     => 'integer',
                                                                 'required' => true ) ) );

$FunctionList['tags_by_keyword'] = array( 'name'            => 'tags_by_keyword',
                                          'operation_types' => array( 'read' ),
                                          'call_method'     => array( 'class'  => 'eZTagsFunctionCollection',
                                                                      'method' => 'fetchTagsByKeyword' ),
                                          'parameter_type'  => 'standard',
                                          'parameters'      => array( array( 'name'     => 'keyword',
                                                                             'type'     => 'string',
                                                                             'required' => true ) ) );

?>
