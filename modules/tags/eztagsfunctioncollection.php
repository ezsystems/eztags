<?php

/**
 * eZTagsFunctionCollection class implements fetch functions for eztags
 * 
 */
class eZTagsFunctionCollection
{
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
