<?php

/**
 * eZTagsObject class inherits eZPersistentObject class
 * to be able to access eztags database table through API
 * 
 */
class eZTagsObject extends eZPersistentObject
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
     * Returns the definition array for eZTagsObject
     * 
     * @return array
     */
    static function definition()
    {
        return array( 'fields' => array( 'id' => array( 'name' => 'ID',
                                                        'datatype' => 'integer',
                                                        'default' => 0,
                                                        'required' => true ),
                                         'parent_id' => array( 'name' => 'ParentID',
                                                             'datatype' => 'integer',
                                                             'default' => null,
                                                             'required' => false ),
                                         'keyword' => array( 'name' => 'Keyword',
                                                             'datatype' => 'string',
                                                             'default' => '',
                                                             'required' => false ),
                                         'modified' => array( 'name' => 'Modified',
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => false ) ),
                      'function_attributes' => array( 'parent' => 'getParent',
                                                      'icon' => 'getIcon' ),
                      'keys' => array( 'id' ),
                      'increment_key' => 'id',
                      'class_name' => 'eZTagsObject',
                      'sort' => array( 'keyword' => 'asc' ),
                      'name' => 'eztags' );
    }

    /**
     * Returns tag parent
     * 
     * @return eZTagsObject
     */
	function getParent()
	{
		return eZPersistentObject::fetchObject( eZTagsObject::definition(), null, array('id' => $this->ParentID) );
	}

    /**
     * Returns weather tag has a parent
     * 
     * @return bool
     */
	function hasParent()
	{
		return eZPersistentObject::count( eZTagsObject::definition(), array('id' => $this->ParentID) );
	}

    /**
     * Returns icon associated with the tag, while respecting hierarchy structure
     * 
     * @return string
     */
	function getIcon()
	{
		$ini = eZINI::instance( 'eztags.ini' );

		$iconMap = $ini->variable( 'Icons', 'IconMap' );

		$returnValue = $ini->variable( 'Icons', 'Default' );

		if(array_key_exists($this->ID, $iconMap) && strlen($iconMap[$this->ID]) > 0)
		{
			$returnValue = $iconMap[$this->ID];
		}
		else
		{
			$tempTag = $this;
			while($tempTag->ParentID > 0)
			{
				$tempTag = $tempTag->getParent();
				if(array_key_exists($tempTag->ID, $iconMap) && strlen($iconMap[$tempTag->ID]) > 0)
				{
					$returnValue = $iconMap[$tempTag->ID];
					break;
				}
			}
		}

		return $returnValue;
	}

    /**
     * Returns eZTagsObject for given ID
     * 
     * @static
     * @param integer $id
     * @return eZTagsObject
     */
	static function fetch($id)
	{
		return eZPersistentObject::fetchObject( eZTagsObject::definition(), null, array('id' => $id) );
	}

    /**
     * Returns array of eZTagsObject objects for given parent ID
     * 
     * @static
     * @param integer $parentID
     * @return array
     */	
	static function fetchByParentID($parentID)
	{
		return eZPersistentObject::fetchObjectList( eZTagsObject::definition(), null, array('parent_id' => $parentID) );
	}

    /**
     * Returns count of eZTagsObject objects for given parent ID
     * 
     * @static
     * @param integer $parentID
     * @return integer
     */	
	static function childrenCountByParentID($parentID)
	{
		return eZPersistentObject::count( eZTagsObject::definition(), array('parent_id' => $parentID) );
	}

    /**
     * Returns array of eZTagsObject objects for given fetch parameters
     * 
     * @static
     * @param mixed $param
     * @return array
     */		
	static function fetchByKeyword($param)
	{
		return eZPersistentObject::fetchObjectList( eZTagsObject::definition(), null, array('keyword' => $param) );
	}
}

?>
