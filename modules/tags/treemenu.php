<?php

function washJS( $string )
{
    return str_replace( array( "\\", "/", "\n", "\t", "\r", "\b", "\f", '"' ), array( '\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"' ), $string );
}

function arrayToJSON( $array )
{
    if ( $array )
    {
        $result = array();
        $resultDict = array();
        $isDict = false;
        $index = 0;
        foreach( $array as $key => $value )
        {
            if ( $key != $index++ )
            {
                $isDict = true;
            }

            if ( is_array( $value ) )
            {
                $value = arrayToJSON( $value );
            }
            else if ( !is_numeric( $value ) or $key == 'name' )
            {
                $value = '"' . washJS( $value ) . '"';
            }

            $result[] = $value;
            $resultDict[] = '"' . washJS( $key ) . '":' . $value;
        }
        if ( $isDict )
        {
            return '{' . implode( $resultDict, ',' ) . '}';
        }
        else
        {
            return '[' . implode( $result, ',' ) . ']';
        }
    }
    else
    {
        return '[]';
    }
}

function lookupIcon($ini, $tag)
{
	$iconMap = $ini->variable( 'Icons', 'IconMap' );

	$returnValue = '';

	if(array_key_exists($tag->ID, $iconMap) && strlen($iconMap[$tag->ID]) > 0)
	{
		$returnValue = $iconMap[$tag->ID];
	}
	else
	{
		$tempTag = $tag;
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

while ( @ob_end_clean() );

$tagID = $Params['TagID'];

$siteINI = eZINI::instance();
$eztagsINI = eZINI::instance( 'eztags.ini' );

if ( is_numeric($TagID) && $TagID >= 0 )
{
	$tag = eZTagsObject::fetch($tagID);

    if(!$tag && $TagID > 0)
    {
        header( $_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found' );
    }
	else
	{
	    $children = eZTagsObject::fetchByParentID($tagID);

	    $response = array();
	    $response['error_code'] = 0;
	    $response['id'] = $tagID;
	    $response['parent_id'] = ($tag) ? $tag->ParentID : -1;
	    $response['children_count'] = count( $children );
	    $response['children'] = array();

	    foreach ( $children as $child )
	    {
	        $childResponse = array();
	        $childResponse['id'] = $child->ID;
	        $childResponse['parent_id'] = $child->ParentID;
	        $childResponse['has_children'] = ( eZTagsObject::childrenCountByParentID($child->ID) ) ? 1 : 0;
	        $childResponse['synonyms_count'] = eZTagsObject::synonymsCount($child->ID);
	        $childResponse['subtree_limitations_count'] = $child->getSubTreeLimitationsCount();
	        $childResponse['keyword'] = $child->Keyword;
	        $childResponse['url'] = 'tags/id/' . $child->ID;
	        $childResponse['icon'] = lookupIcon($eztagsINI, $child);
	        eZURI::transformURI( $childResponse['url'] );
	        $childResponse['modified'] = $child->Modified;
	        $response['children'][] = $childResponse;
	    }
	    $httpCharset = eZTextCodec::httpCharset();

	    $jsonText= arrayToJSON( $response );

	    $codec = eZTextCodec::instance( $httpCharset, 'unicode' );
	    $jsonTextArray = $codec->convertString( $jsonText );
	    $jsonText = '';
	    foreach ( $jsonTextArray as $character )
	    {
	        if ( $character < 128 )
	        {
	            $jsonText .= chr( $character );
	        }
	        else
	        {
	            $jsonText .= '\u' . str_pad( dechex( $character ), 4, '0000', STR_PAD_LEFT );
	        }
	    }

	    header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', 0 ) . ' GMT' );
	    header( 'Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0' );
	    header( 'Pragma: no-cache' );
	    header( 'Content-Type: application/json' );
	    header( 'Content-Length: '.strlen( $jsonText ) );

	    echo $jsonText;
	}
}
else
{
	header( $_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found' );
}

eZExecution::cleanExit();

?>
