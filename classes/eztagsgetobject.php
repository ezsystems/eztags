<?php
class eZTagsGetObject
{
    function eZTagsGetObject()
    {
    }

	static public function fetchTagObject( $keyword )
	{
		return array( 'result' => eZPersistentObject::fetchObject( eZTagsObject::definition(), null, array('keyword' => $keyword) ) );
	}
}

?>
