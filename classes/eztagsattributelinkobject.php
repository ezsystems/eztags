<?php

/**
 * eZTagsAttributeLinkObject class inherits eZPersistentObject class
 * to be able to access eztags_attribute_link database table through API
 * 
 */
class eZTagsAttributeLinkObject extends eZPersistentObject
{
    /**
     * Constructor
     * 
     */
    function __construct( $row )
    {
        parent::__construct( $row );
    }

    /**
     * Returns the definition array for eZTagsAttributeLinkObject
     * 
     * @return array
     */
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
                                                             'required' => true ),
                                         'object_id' => array( 'name' => 'ObjectID',
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => true ) ),
                      'keys' => array( 'id' ),
                      'increment_key' => 'id',
                      'class_name' => 'eZTagsAttributeLinkObject',
                      'sort' => array( 'id' => 'asc' ),
                      'name' => 'eztags_attribute_link' );
    }

    /**
     * Fetches the array of eZTagsAttributeLinkObject objects based on provided keyword ID
     * 
     * @param integer $keywordID
     * @return array
     */
    static function fetchByKeywordID($keywordID)
    {
    	return eZPersistentObject::fetchObjectList( eZTagsAttributeLinkObject::definition(), null, array('keyword_id' => $keywordID) );
    }
}

?>
