<?php
class eZTagsAttributeLinkObject extends eZPersistentObject
{
    function __construct( $row )
    {
        parent::__construct( $row );
    }

    static function definition()
    {
        return array( 'fields' => array( 'id' => array( 'name' => 'ID',
                                                        'datatype' => 'integer',
                                                        'default' => 0,
                                                        'required' => true ),
                                         'keyword_id' => array( 'name' => 'KeywordID',
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => true ),
                                         'objectattribute_id' => array( 'name' => 'ObjectAttributeID',
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => true ) ),
                      'keys' => array( 'id' ),
                      'increment_key' => 'id',
                      'class_name' => 'eZTagsAttributeLinkObject',
                      'sort' => array( 'id' => 'asc' ),
                      'name' => 'eztags_attribute_link' );
    }
}

?>
