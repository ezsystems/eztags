<?php

/**
 * eZTagsAttributeFilter class implements TagsAttributeFilter extended attribute
 * 
 */
class eZTagsAttributeFilter
{
    /**
     * Creates and returns SQL parts used in fetch functions
     * 
     * @return array
     */
    function createSqlParts( $params )
    {
        $returnArray = array( 'tables' => '', 'joins'  => '', 'columns' => '' );

		if(isset($params['tag_id']) && is_numeric($params['tag_id']) && $params['tag_id'] > 0)
		{
			$tagID = $params['tag_id'];

			$returnArray['tables'] = ", eztags_attribute_link i1 ";
			$returnArray['joins'] = " i1.keyword_id = $tagID AND i1.object_id = ezcontentobject.id AND ";
		}

		return $returnArray;
    }
}

?>
