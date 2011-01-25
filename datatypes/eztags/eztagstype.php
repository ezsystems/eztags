<?php

/**
 * eZTagsType class implements the eztags datatype
 * 
 */
class eZTagsType extends eZDataType
{
    const DATA_TYPE_STRING = 'eztags';
    const SUBTREE_LIMIT_VARIABLE = '_eztags_subtree_limit_';
    const SUBTREE_LIMIT_FIELD = 'data_int1';

	const SHOW_DROPDOWN_VARIABLE = '_eztags_show_dropdown_';
	const SHOW_DROPDOWN_FIELD = 'data_int2';

    /**
     * Constructor
     * 
     */
    function __construct()
    {
        parent::__construct( self::DATA_TYPE_STRING, ezpI18n::tr( 'extension/eztags/datatypes', 'Tags' ),
                           array( 'serialize_supported' => true ) );
    }

    /**
     * Sets the default value
     * 
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @param eZContentObjectVersion $currentVersion
     * @param eZContentObjectAttribute $originalContentObjectAttribute
     */
    function initializeObjectAttribute( $contentObjectAttribute, $currentVersion, $originalContentObjectAttribute )
    {
        if ( $currentVersion != false )
        {
            $originalContentObjectAttributeID = $originalContentObjectAttribute->attribute( 'id' );
            $contentObjectAttributeID = $contentObjectAttribute->attribute( 'id' );

            // if translating or copying an object
            if ( $originalContentObjectAttributeID != $contentObjectAttributeID )
            {
                // copy keywords links as well
                $keyword = $originalContentObjectAttribute->content();
                if ( $keyword instanceof eZTags )
                {
                    $keyword->store( $contentObjectAttribute );
                }
            }

            $subTreeLimit = $originalContentObjectAttribute->attribute( self::SUBTREE_LIMIT_FIELD );
            $contentObjectAttribute->setAttribute( self::SUBTREE_LIMIT_FIELD, $subTreeLimit );

            $showDropdown = $originalContentObjectAttribute->attribute( self::SHOW_DROPDOWN_FIELD );
            $contentObjectAttribute->setAttribute( self::SHOW_DROPDOWN_FIELD, $showDropdown );
        }
    }

    /**
     * Validates the input and returns true if the input was valid for this datatype
     * 
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @return bool
     */
    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $classAttribute = $contentObjectAttribute->contentClassAttribute();

        if ( $http->hasPostVariable( $base . '_eztags_data_text_' . $contentObjectAttribute->attribute( 'id' ) ) &&
             $http->hasPostVariable( $base . '_eztags_data_text2_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $data = trim($http->postVariable( $base . '_eztags_data_text_' . $contentObjectAttribute->attribute( 'id' ) ));
            $data2 = trim($http->postVariable( $base . '_eztags_data_text2_' . $contentObjectAttribute->attribute( 'id' ) ));

            if ( $data == "" )
            {
                if ( !$classAttribute->attribute( 'is_information_collector' ) and $contentObjectAttribute->validateIsRequired() )
                {
                    $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes',
                                                                         'Input required.' ) );
                    return eZInputValidator::STATE_INVALID;
                }
            }

            if ( $data != "" && $data2 == "" )
            {
                $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes',
                                                                          'Input required.' ) );
                return eZInputValidator::STATE_INVALID;
            }
        }
        else if ( !$classAttribute->attribute( 'is_information_collector' ) and $contentObjectAttribute->validateIsRequired() )
        {
            $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'Input required.' ) );
            return eZInputValidator::STATE_INVALID;
        }
        return eZInputValidator::STATE_ACCEPTED;
    }

    /**
     * Fetches the http post var keyword input and stores it in the data instance
     * 
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @return bool
     */
    function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . '_eztags_data_text_' . $contentObjectAttribute->attribute( 'id' ) ) &&
             $http->hasPostVariable( $base . '_eztags_data_text2_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $data = $http->postVariable( $base . '_eztags_data_text_' . $contentObjectAttribute->attribute( 'id' ) );
            $data2 = $http->postVariable( $base . '_eztags_data_text2_' . $contentObjectAttribute->attribute( 'id' ) );
            $keyword = new eZTags();
            $keyword->initializeKeyword( $data, $data2 );
            $contentObjectAttribute->setContent( $keyword );
            return true;
        }
        return false;
    }

    /**
     * Stores the object attribute
     * 
     * @param eZContentObjectAttribute $attribute
     */
    function storeObjectAttribute( $attribute )
    {
        // create keyword index
        $keyword = $attribute->content();
        if ( is_object( $keyword ) )
        {
            $keyword->store( $attribute );
        }
    }

    /**
     * Validates class attribute HTTP input
     * 
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentClassAttribute $attribute
     * @return bool
     */
    function validateClassAttributeHTTPInput( $http, $base, $attribute )
    {
    	$subTreeLimitName = $base . self::SUBTREE_LIMIT_VARIABLE . $attribute->attribute( 'id' );
    	if( !$http->hasPostVariable( $subTreeLimitName ) || !is_numeric( $http->postVariable( $subTreeLimitName ) ) || $http->postVariable( $subTreeLimitName ) < 0 )
    	{
    		return eZInputValidator::STATE_INVALID;
    	}

		$subTreeLimit = $http->postVariable( $subTreeLimitName );

		$tag = eZTagsObject::fetch($subTreeLimit);

		if ( !( $tag instanceof eZTagsObject ) && $subTreeLimit > 0 )
		{
			return eZInputValidator::STATE_INVALID;
		}

		if( $subTreeLimit > 0 && $tag->MainTagID > 0)
		{
			return eZInputValidator::STATE_INVALID;
		}

        return eZInputValidator::STATE_ACCEPTED;
    }

    /**
     * Fetches class attribute HTTP input and stores it
     * 
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentClassAttribute $attribute
     * @return bool
     */
    function fetchClassAttributeHTTPInput( $http, $base, $attribute )
    {
    	$subTreeLimitName = $base . self::SUBTREE_LIMIT_VARIABLE . $attribute->attribute( 'id' );
    	if( !$http->hasPostVariable( $subTreeLimitName ) )
    	{
    		return false;
    	}

    	$data = $http->postVariable( $subTreeLimitName );
		$data2 = 0;
		if( $http->hasPostVariable( $base . self::SHOW_DROPDOWN_VARIABLE . $attribute->attribute( 'id' ) ) )
		{
			$data2 = 1;
		}

		$attribute->setAttribute(self::SUBTREE_LIMIT_FIELD, $data);
		$attribute->setAttribute(self::SHOW_DROPDOWN_FIELD, $data2);
        return true;
    }

    /**
     * Returns the content
     * 
     * @param eZContentObjectAttribute $attribute
     * @return eZTags
     */
    function objectAttributeContent( $attribute )
    {
        $keyword = new eZTags();
        $keyword->fetch( $attribute );

        return $keyword;
    }

    /**
     * Returns the meta data used for storing search indeces
     * 
     * @param eZContentObjectAttribute $attribute
     * @return string
     */
    function metaData( $attribute )
    {
        $keyword = new eZTags();
        $keyword->fetch( $attribute );
        $return = $keyword->keywordString();

        return $return;
    }

    /**
     * Delete stored object attribute
     * 
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @param eZContentObjectVersion $version
     */
    function deleteStoredObjectAttribute( $contentObjectAttribute, $version = null )
    {
        if ( $version != null ) // Do not delete if discarding draft
        {
            return;
        }

        $contentObjectAttributeID = $contentObjectAttribute->attribute( "id" );

        $db = eZDB::instance();

        /* First we retrieve all the keyword ID related to this object attribute */
        $res = $db->arrayQuery( "SELECT keyword_id
                                 FROM eztags_attribute_link
                                 WHERE objectattribute_id='$contentObjectAttributeID'" );
        if ( !count ( $res ) )
        {
            /* If there are no keywords at all, we abort the function as there
             * is nothing more to do */
            return;
        }

        /* We remove the link between the keyword and the object attribute to be removed */
        $db->query( "DELETE FROM eztags_attribute_link
                     WHERE objectattribute_id='$contentObjectAttributeID'" );
    }

    /**
     * Returns the content of the keyword for use as a title
     * 
     * @param eZContentObjectAttribute $attribute
     * @param string $name
     * @return string
     */
    function title( $attribute, $name = null )
    {
        $keyword = new eZTags();
        $keyword->fetch( $attribute );
        $return = $keyword->keywordString();

        return $return;
    }

    /**
     * Returns true if content object attribute has content
     * 
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @return bool
     */
    function hasObjectAttributeContent( $contentObjectAttribute )
    {
        $keyword = new eZTags();
        $keyword->fetch( $contentObjectAttribute );
        $array = $keyword->keywordArray();

        return count( $array ) > 0;
    }

    /**
     * Returns if the content is indexable
     * 
     * @return bool
     */
    function isIndexable()
    {
        return true;
    }

    /**
     * Returns string representation of a content object attribute
     * 
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @return string
     */
    function toString( $contentObjectAttribute )
    {
        $keyword = new eZTags();
        $keyword->fetch( $contentObjectAttribute  );
        return  $keyword->keywordString();
    }

    /**
     * Creates the content object attribute content from the input string
     * 
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @param string $string
     * @return bool
     */
    function fromString( $contentObjectAttribute, $string )
    {
        if ( $string != '' )
        {
            $keyword = new eZTags();
            $keyword->initializeKeyword( $string );
            $contentObjectAttribute ->setContent( $keyword );
        }
        return true;
    }

    /**
     * Serializes the content object attribute
     * 
     * @param eZPackage $package
     * @param eZContentObjectAttribute $objectAttribute
     * @return DOMNode
     */
    function serializeContentObjectAttribute( $package, $objectAttribute )
    {
        $node = $this->createContentObjectAttributeDOMNode( $objectAttribute );

        $keyword = new eZTags();
        $keyword->fetch( $objectAttribute );
        $dom = $node->ownerDocument;
        $keywordStringNode = $dom->createElement( 'keyword-string' );
        $keywordStringNode->appendChild( $dom->createTextNode( $keyword->keywordString() ) );
        $node->appendChild( $keywordStringNode );
        $parentStringNode = $dom->createElement( 'parent-string' );
        $parentStringNode->appendChild( $dom->createTextNode( $keyword->parentString() ) );
        $node->appendChild( $parentStringNode );

        return $node;
    }

    /**
     * Deserializes the content object attribute from provided DOM node
     * 
     * @param eZPackage $package
     * @param eZContentObjectAttribute $objectAttribute
     * @param DOMNode $attributeNode
     */
    function unserializeContentObjectAttribute( $package, $objectAttribute, $attributeNode )
    {
        $keyWordString = $attributeNode->getElementsByTagName( 'keyword-string' )->item( 0 )->textContent;
        $parentString = $attributeNode->getElementsByTagName( 'parent-string' )->item( 0 )->textContent;
        $keyword = new eZTags();
        $keyword->initializeKeyword( $keyWordString, $parentString );
        $objectAttribute->setContent( $keyword );
    }

    /**
     * Returns if the content supports batch initialization
     * 
     * @return bool
     */
    function supportsBatchInitializeObjectAttribute()
    {
        return true;
    }

    /**
     * Sets grouped_input to true for edit view of the datatype
     * 
     * @return array
     */
    function objectDisplayInformation( $objectAttribute, $mergeInfo = false )
    {
        $info = array( 'edit' => array( 'grouped_input' => true ) );
        return eZDataType::objectDisplayInformation( $objectAttribute, $info );
    }
}

eZDataType::register( eZTagsType::DATA_TYPE_STRING, 'eZTagsType' );

?>
