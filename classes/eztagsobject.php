<?php

/**
 * eZTagsObject class inherits eZPersistentObject class
 * to be able to access eztags database table through API
 * 
 */
class eZTagsObject extends eZPersistentObject
{
	public $TagAttributeLinks = null;

    /**
     * Constructor
     * 
     */
    function __construct( $row )
    {
        parent::__construct( $row );
        
        $this->TagAttributeLinks = eZTagsAttributeLinkObject::fetchByKeywordID($this->ID);
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
                                         'main_tag_id' => array( 'name' => 'MainTagID',
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
                                                      'children' => 'getChildren',
                                                      'related_objects' => 'getRelatedObjects',
                                                      'main_tag' => 'getMainTag',
                                                      'synonyms' => 'getSynonyms',
                                                      'icon' => 'getIcon' ),
                      'keys' => array( 'id' ),
                      'increment_key' => 'id',
                      'class_name' => 'eZTagsObject',
                      'sort' => array( 'keyword' => 'asc' ),
                      'name' => 'eztags' );
    }

    /**
     * Returns whether tag has a parent
     * 
     * @return bool
     */
	function hasParent()
	{
		return eZPersistentObject::count( eZTagsObject::definition(), array('id' => $this->ParentID) );
	}

    /**
     * Returns whether tag is related to object defined by eztags attribute id and object id
     * 
     * @param integer $objectAttributeID
     * @param integer $objectID
     * @return bool
     */
	function isRelatedToObject($objectAttributeID, $objectID)
	{
		foreach($this->TagAttributeLinks as $link)
		{
			if($link->ObjectAttributeID == $objectAttributeID && $link->ObjectID == $objectID)
			{
				return true;
			}
		}
		
		return false;
	}

    /**
     * Returns tag parent
     * 
     * @return eZTagsObject
     */
	function getParent()
	{
		return eZTagsObject::fetch($this->ParentID);
	}

    /**
     * Returns first level children tags
     * 
     * @return array
     */
	function getChildren()
	{
		return eZTagsObject::fetchByParentID($this->ID);
	}

    /**
     * Returns objects related to this tag
     * 
     * @return array
     */
	function getRelatedObjects()
	{
		if(count($this->TagAttributeLinks) > 0)
		{
			$objectIDArray = array();
			foreach($this->TagAttributeLinks as $tagAttributeLink)
			{
				array_push($objectIDArray, $tagAttributeLink->ObjectID);
			}
	
			return eZContentObject::fetchIDArray($objectIDArray);
		}
		
		return array();
	}

    /**
     * Returns the main tag for synonym
     * 
     * @return eZTagsObject
     */
	function getMainTag()
	{
		return eZTagsObject::fetch($this->MainTagID);
	}

    /**
     * Returns synonyms for the tag
     * 
     * @return array
     */
	function getSynonyms()
	{
		return eZTagsObject::fetchSynonyms( $this->ID );
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

		if($this->MainTagID > 0)
		{
			$variableName = 'MainTagID';
		}
		else
		{
			$variableName = 'ID';
		}

		if(array_key_exists($this->$variableName, $iconMap) && strlen($iconMap[$this->$variableName]) > 0)
		{
			$returnValue = $iconMap[$this->$variableName];
		}
		else
		{
			$tempTag = $this;
			while($tempTag->ParentID > 0)
			{
				$tempTag = $tempTag->getParent();
				if(array_key_exists($tempTag->$variableName, $iconMap) && strlen($iconMap[$tempTag->$variableName]) > 0)
				{
					$returnValue = $iconMap[$tempTag->$variableName];
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
		return eZPersistentObject::fetchObjectList( eZTagsObject::definition(), null, array('parent_id' => $parentID, 'main_tag_id' => 0) );
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
		return eZPersistentObject::count( eZTagsObject::definition(), array('parent_id' => $parentID, 'main_tag_id' => 0) );
	}

    /**
     * Returns array of eZTagsObject objects that are synonyms of provided tag ID
     * 
     * @static
     * @param integer $mainTagID
     * @return array
     */		
	static function fetchSynonyms($mainTagID)
	{
		return eZPersistentObject::fetchObjectList( eZTagsObject::definition(), null, array('main_tag_id' => $mainTagID) );
	}

    /**
     * Returns count of eZTagsObject objects that are synonyms of provided tag ID
     * 
     * @static
     * @param integer $mainTagID
     * @return integer
     */		
	static function synonymsCount($mainTagID)
	{
		return eZPersistentObject::count( eZTagsObject::definition(), array('main_tag_id' => $mainTagID) );
	}

    /**
     * Returns array of eZTagsObject objects for given keyword
     * 
     * @static
     * @param mixed $param
     * @return array
     */		
	static function fetchByKeyword($param)
	{
		return eZPersistentObject::fetchObjectList( eZTagsObject::definition(), null, array('keyword' => $param) );
	}

    /**
     * Recursively deletes all children tags of the given tag, including the given tag itself
     * 
     * @static
     * @param eZTagsObject $rootTag
     */
	static function recursiveTagDelete($rootTag)
	{
		$children = eZTagsObject::fetchByParentID($rootTag->ID);
	
		foreach($children as $child)
		{
			recursiveTagDelete($child);
		}
	
		foreach($rootTag->TagAttributeLinks as $tagAttributeLink)
		{
			$tagAttributeLink->remove();
		}

		$synonyms = $rootTag->getSynonyms();
		foreach($synonyms as $synonym)
		{
			foreach($synonym->TagAttributeLinks as $tagAttributeLink)
			{
				$tagAttributeLink->remove();
			}
			
			$synonym->remove();
		}

		$rootTag->remove();
	}
}

?>
