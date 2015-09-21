<?php

/**
 * eZTagsAttributeLinkObject class inherits eZPersistentObject class
 * to be able to access eztags_attribute_link database table through API
 */
class eZTagsAttributeLinkObject extends eZPersistentObject
{
    /**
     * Constructor
     *
     * @param array $row
     */
    function __construct( $row )
    {
        parent::eZPersistentObject( $row );
    }

    /**
     * Returns the definition array for eZTagsAttributeLinkObject
     *
     * @return array
     */
    static public function definition()
    {
        return array( 'fields'        => array( 'id'                      => array( 'name'     => 'ID',
                                                                                    'datatype' => 'integer',
                                                                                    'default'  => 0,
                                                                                    'required' => true ),
                                                'keyword_id'              => array( 'name'     => 'KeywordID',
                                                                                    'datatype' => 'integer',
                                                                                    'default'  => 0,
                                                                                    'required' => true ),
                                                'objectattribute_id'      => array( 'name'     => 'ObjectAttributeID',
                                                                                    'datatype' => 'integer',
                                                                                    'default'  => 0,
                                                                                    'required' => true ),
                                                'objectattribute_version' => array( 'name'     => 'ObjectAttributeVersion',
                                                                                    'datatype' => 'integer',
                                                                                    'default'  => 0,
                                                                                    'required' => true ),
                                                'object_id'               => array( 'name'     => 'ObjectID',
                                                                                    'datatype' => 'integer',
                                                                                    'default'  => 0,
                                                                                    'required' => true ),
                                                'priority'                => array( 'name'     => 'Priority',
                                                                                    'datatype' => 'integer',
                                                                                    'default'  => 0,
                                                                                    'required' => true ) ),
                      'keys'          => array( 'id' ),
                      'increment_key' => 'id',
                      'class_name'    => 'eZTagsAttributeLinkObject',
                      'sort'          => array( 'objectattribute_id' => 'asc', 'objectattribute_version' => 'asc', 'priority' => 'asc' ),
                      'name'          => 'eztags_attribute_link' );
    }

    /**
     * Fetches the array of eZTagsAttributeLinkObject objects based on provided tag ID
     *
     * @static
     *
     * @param int $tagID
     *
     * @return eZTagsAttributeLinkObject[]
     */
    static public function fetchByTagID( $tagID )
    {
        $objects = parent::fetchObjectList( self::definition(), null, array( 'keyword_id' => $tagID ) );

        if ( is_array( $objects ) )
            return $objects;

        return array();
    }

    /**
     * Fetches the eZTagsAttributeLinkObject object based on provided content object params and keyword ID
     *
     * @static
     *
     * @param int $objectAttributeID
     * @param int $objectAttributeVersion
     * @param int $objectID
     * @param int $keywordID
     *
     * @return eZTagsAttributeLinkObject if found, false otherwise
     */
    static public function fetchByObjectAttributeAndKeywordID( $objectAttributeID, $objectAttributeVersion, $objectID, $keywordID )
    {
        $objects = parent::fetchObjectList( self::definition(), null,
                                                        array( 'objectattribute_id'      => $objectAttributeID,
                                                               'objectattribute_version' => $objectAttributeVersion,
                                                               'object_id'               => $objectID,
                                                               'keyword_id'              => $keywordID ) );

        if ( is_array( $objects ) && !empty( $objects ) )
            return $objects[0];

        return false;
    }

    /**
     * Removes the objects from persistence which are related to content object attribute
     * defined by attribute ID and attribute version
     *
     * @static
     *
     * @param int $objectAttributeID
     * @param int|null $objectAttributeVersion
     */
    static public function removeByAttribute( $objectAttributeID, $objectAttributeVersion = null )
    {
        if ( !is_numeric( $objectAttributeID ) )
            return;

        $conditions = array( 'objectattribute_id' => (int) $objectAttributeID );
        if ( is_numeric( $objectAttributeVersion ) )
            $conditions['objectattribute_version'] = (int) $objectAttributeVersion;

        parent::removeObject( self::definition(), $conditions );
    }
}
