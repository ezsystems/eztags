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
			$tagIDsArray = array($params['tag_id']);

			if(!isset($params['include_synonyms']) || (isset($params['include_synonyms']) && $params['include_synonyms'] == true))
			{
				$tag = eZTagsObject::fetch($params['tag_id']);
				if($tag instanceof eZTagsObject)
				{
					foreach($tag->getSynonyms() as $synonym)
					{
						$tagIDsArray[] = $synonym->ID;
					}
				}
			}

			$returnArray['tables'] = ", eztags_attribute_link i1 ";

			$db = eZDB::instance();
			$dbString = $db->generateSQLINStatement($tagIDsArray, 'i1.keyword_id', false, true, 'int');

			$returnArray['joins'] = " $dbString AND i1.object_id = ezcontentobject.id AND ";
		}

		return $returnArray;
    }
}

?>
