<?php
class eZTagsObject extends eZPersistentObject
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

	function getParent()
	{
		return eZPersistentObject::fetchObject( eZTagsObject::definition(), null, array('id' => $this->ParentID) );
	}

	function hasParent()
	{
		return eZPersistentObject::count( eZTagsObject::definition(), array('id' => $this->ParentID) );
	}

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
}

?>
