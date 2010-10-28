<?php

/**
 * eZTagsGetObject class implements object fetch of tags module
 * 
 */
class eZTagsGetObject
{
    /**
     * Fetches first object associated with provided keyword
     * 
     * @static
     * @param string $keyword
     * @return array
     */
	static public function fetchTagObject( $keyword )
	{
		$result = eZTagsObject::fetchByKeyword($keyword);
		
		if(is_array($result) && count($result) > 0)
			return array( 'result' => $result[0] );
		else
			return array( 'result' => null );
	}
}

?>
