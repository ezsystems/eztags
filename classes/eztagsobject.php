<?php

/**
 * eZTagsObject class inherits eZPersistentObject class
 * to be able to access eztags database table through API
 * 
 */
class eZTagsObject extends eZPersistentObject
{
	public $TagAttributeLinks = null;

	const LOCK_STATUS_UNLOCKED = 0;
	const LOCK_STATUS_HARD_LOCK = 1;
	const LOCK_STATUS_SOFT_LOCK = 2;

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
                                         'path_string' => array( 'name' => 'PathString',
                                                             'datatype' => 'string',
                                                             'default' => '',
                                                             'required' => false ),
                                         'modified' => array( 'name' => 'Modified',
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => false ) ),
                      'function_attributes' => array( 'parent' => 'getParent',
                                                      'children' => 'getChildren',
                                                      'children_count' => 'getChildrenCount',
                                                      'related_objects' => 'getRelatedObjects',
                                                      'subtree_limitations' => 'getSubTreeLimitations',
                                                      'subtree_limitations_count' => 'getSubTreeLimitationsCount',
                                                      'lock_status' => 'getLockStatus',
                                                      'main_tag' => 'getMainTag',
                                                      'synonyms' => 'getSynonyms',
                                                      'synonyms_count' => 'getSynonymsCount',
                                                      'icon' => 'getIcon' ),
                      'keys' => array( 'id' ),
                      'increment_key' => 'id',
                      'class_name' => 'eZTagsObject',
                      'sort' => array( 'keyword' => 'asc' ),
                      'name' => 'eztags' );
    }

    /**
     * Updates path string of the tag and all of it's children and synonyms.
     * 
     * @param eZTagsObject $parentTag
     */
	function updatePathString($parentTag)
	{
		$this->PathString = (($parentTag instanceof eZTagsObject) ? $parentTag->PathString : '/') . $this->ID . '/';
		$this->store();
		
		foreach($this->getSynonyms() as $s)
		{
			$s->PathString = (($parentTag instanceof eZTagsObject) ? $parentTag->PathString : '/') . $s->ID . '/';
			$s->store();
		}
		
		foreach($this->getChildren() as $c)
		{
			$c->updatePathString($this);
		}
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
     * Returns count of first level children tags
     * 
     * @return integer
     */
	function getChildrenCount()
	{
		return eZTagsObject::childrenCountByParentID($this->ID);
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
     * Returns list of eZContentClassAttribute objects (represented as subtree limitations)
     * 
     * @return array
     */
	function getSubTreeLimitations()
	{
		if($this->MainNodeID == 0)
		{
			return eZPersistentObject::fetchObjectList(eZContentClassAttribute::definition(), null,
																	array('data_type_string' => 'eztags',
																		eZTagsType::SUBTREE_LIMIT_FIELD => $this->ID,
																		'version' => eZContentClass::VERSION_STATUS_DEFINED));
		}
		else
		{
			return array();
		}
	}

    /**
     * Returns count of eZContentClassAttribute objects (represented as subtree limitation count)
     * 
     * @return integer
     */
	function getSubTreeLimitationsCount()
	{
		if($this->MainNodeID == 0)
		{
			return eZPersistentObject::count(eZContentClassAttribute::definition(),
																	array('data_type_string' => 'eztags',
																		eZTagsType::SUBTREE_LIMIT_FIELD => $this->ID,
																		'version' => eZContentClass::VERSION_STATUS_DEFINED));
		}
		else
		{
			return 0;
		}
	}

    /**
     * Returns the lock status of current tag
     * - unlocked - no subtree limitations
     * - hard lock - tag is used as subtree limitation
     * - soft lock - parent tag is used as subtree limitation
     * 
     * @return integer
     */
	function getLockStatus()
	{
		$retValue = self::LOCK_STATUS_UNLOCKED;

		if($this->getSubTreeLimitationsCount() > 0)
		{
			$retValue = self::LOCK_STATUS_HARD_LOCK;
		}
		else
		{
			$tag = $this;
			while($tag->ParentID > 0)
			{
				$tag = $tag->getParent();
				if($tag->getSubTreeLimitationsCount() > 0)
				{
					$retValue = self::LOCK_STATUS_SOFT_LOCK;
					break;
				}
			}
		}

		return $retValue;
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
     * Returns synonym count for the tag
     * 
     * @return array
     */
	function getSynonymsCount()
	{
		return eZTagsObject::synonymsCount( $this->ID );
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
		$defaultIcon = $ini->variable( 'Icons', 'Default' );

		if($this->MainTagID > 0)
		{
			$tag = $this->getMainTag();
		}
		else
		{
			$tag = $this;
		}

		if(array_key_exists($tag->ID, $iconMap) && strlen($iconMap[$tag->ID]) > 0)
		{
			return $iconMap[$tag->ID];
		}

		while($tag->ParentID > 0)
		{
			$tag = $tag->getParent();
			if(array_key_exists($tag->ID, $iconMap) && strlen($iconMap[$tag->ID]) > 0)
			{
				return $iconMap[$tag->ID];
			}
		}

		return $defaultIcon;
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
     * Returns array of eZTagsObject objects for given params
     * 
     * @static
     * @param array $params
     * @param array $limits
     * @return array
     */	
	static function fetchList($params, $limits = null)
	{
		return eZPersistentObject::fetchObjectList( eZTagsObject::definition(), null, $params, null, $limits );
	}

    /**
     * Returns count of eZTagsObject objects for given params
     * 
     * @static
     * @param mixed $params
     * @return integer
     */	
	static function fetchListCount($params)
	{
		return eZPersistentObject::count( eZTagsObject::definition(), $params );
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
     * Returns the array of eZTagsObject objects for given path string
     * 
     * @static
     * @param string $param
     * @return array
     */		
	static function fetchByPathString($param)
	{
		return eZPersistentObject::fetchObjectList( eZTagsObject::definition(), null, array('path_string' => array('like', $param . '%'), main_tag_id => 0) );
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
