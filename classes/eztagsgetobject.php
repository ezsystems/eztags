<?php
class eZTagsGetObject
{
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
