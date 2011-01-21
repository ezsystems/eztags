<?php

/**
 * eZTagsFunctionCollection class implements fetch functions for eztags
 * 
 */
class eZTagsFunctionCollection
{
    /**
     * Fetches eZTagsObject object for the provided tag ID
     * 
     * @static
     * @param integer $tag_id
     * @return array
     */
	static public function fetchTagObject( $tag_id )
	{
		$result = eZTagsObject::fetch($tag_id);

		if($result instanceof eZTagsObject)
			return array( 'result' => $result );
		else
			return array( 'result' => null );
	}

    /**
     * Fetches first object associated with provided keyword
     * 
     * @static
     * @param string $keyword
     * @return array
     */
	static public function fetchTagObjectByKeyword( $keyword )
	{
		$result = eZTagsObject::fetchByKeyword($keyword);

		if(is_array($result) && count($result) > 0)
			return array( 'result' => $result[0] );
		else
			return array( 'result' => null );
	}
}

?>
