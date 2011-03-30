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
        parent::__construct( self::DATA_TYPE_STRING, ezpI18n::tr( 'extension/eztags/datatypes', 'Tags' ), array( 'serialize_supported' => true ) );
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

            $eztags = $originalContentObjectAttribute->content();
            if ( $eztags instanceof eZTags )
            {
                $eztags->store( $contentObjectAttribute );
            }
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
             $http->hasPostVariable( $base . '_eztags_data_text2_' . $contentObjectAttribute->attribute( 'id' ) ) &&
             $http->hasPostVariable( $base . '_eztags_data_text3_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $data = trim( $http->postVariable( $base . '_eztags_data_text_' . $contentObjectAttribute->attribute( 'id' ) ) );
            $data2 = trim( $http->postVariable( $base . '_eztags_data_text2_' . $contentObjectAttribute->attribute( 'id' ) ) );
            $data3 = trim( $http->postVariable( $base . '_eztags_data_text3_' . $contentObjectAttribute->attribute( 'id' ) ) );

            if ( empty( $data ) || empty( $data2 ) || empty( $data3 ) )
            {
                $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'Input required.' ) );
                return eZInputValidator::STATE_INVALID;
            }
            else
            {
                $dataArray = explode( '|#', $data );
                $data2Array = explode( '|#', $data2 );
                $data3Array = explode( '|#', $data3 );
                if ( count( $data2Array ) != count( $dataArray ) || count( $data3Array ) != count( $dataArray ) )
                {
                    $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'Input required.' ) );
                    return eZInputValidator::STATE_INVALID;
                }
            }
        }
        else if ( !$classAttribute->attribute( 'is_information_collector' ) && $contentObjectAttribute->validateIsRequired() )
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
             $http->hasPostVariable( $base . '_eztags_data_text2_' . $contentObjectAttribute->attribute( 'id' ) ) &&
             $http->hasPostVariable( $base . '_eztags_data_text3_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $data = $http->postVariable( $base . '_eztags_data_text_' . $contentObjectAttribute->attribute( 'id' ) );
            $data2 = $http->postVariable( $base . '_eztags_data_text2_' . $contentObjectAttribute->attribute( 'id' ) );
            $data3 = $http->postVariable( $base . '_eztags_data_text3_' . $contentObjectAttribute->attribute( 'id' ) );

            $eztags = new eZTags();
            $eztags->createFromStrings( $data3, $data, $data2 );
            $contentObjectAttribute->setContent( $eztags );

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
        $eztags = $attribute->content();
        if ( $eztags instanceof eZTags )
        {
            $eztags->store( $attribute );
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
        if ( !$http->hasPostVariable( $subTreeLimitName ) || (int) $http->postVariable( $subTreeLimitName ) < 0 )
        {
            return eZInputValidator::STATE_INVALID;
        }

        $subTreeLimit = (int) $http->postVariable( $subTreeLimitName );

        $tag = eZTagsObject::fetch( $subTreeLimit );

        if ( !( $tag instanceof eZTagsObject ) && $subTreeLimit > 0 )
        {
            return eZInputValidator::STATE_INVALID;
        }

        if ( $subTreeLimit > 0 && $tag->MainTagID > 0 )
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
        if ( !$http->hasPostVariable( $subTreeLimitName ) || (int) $http->postVariable( $subTreeLimitName ) < 0 )
        {
            return false;
        }

        $data = (int) $http->postVariable( $subTreeLimitName );
        $data2 = 0;
        if ( $http->hasPostVariable( $base . self::SHOW_DROPDOWN_VARIABLE . $attribute->attribute( 'id' ) ) )
        {
            $data2 = 1;
        }

        $attribute->setAttribute( self::SUBTREE_LIMIT_FIELD, $data );
        $attribute->setAttribute( self::SHOW_DROPDOWN_FIELD, $data2 );

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
        $eztags = new eZTags();
        $eztags->createFromAttribute( $attribute );

        return $eztags;
    }

    /**
     * Returns the meta data used for storing search indeces
     *
     * @param eZContentObjectAttribute $attribute
     * @return string
     */
    function metaData( $attribute )
    {
        $eztags = new eZTags();
        $eztags->createFromAttribute( $attribute );

        return $eztags->keywordString( ', ' );
    }

    /**
     * Delete stored object attribute
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @param eZContentObjectVersion $version
     */
    function deleteStoredObjectAttribute( $contentObjectAttribute, $version = null )
    {
        $contentObjectAttributeID = $contentObjectAttribute->attribute( "id" );
        $contentObjectAttributeVersion = $contentObjectAttribute->attribute( "version" );

        // We remove the link between the tag and the object attribute to be removed
        $db = eZDB::instance();
        $db->query( "DELETE FROM eztags_attribute_link
                     WHERE objectattribute_id = $contentObjectAttributeID
                     AND objectattribute_version = $contentObjectAttributeVersion" );
    }

    /**
     * Returns the content of eztags attribute for use as a title
     *
     * @param eZContentObjectAttribute $attribute
     * @param string $name
     * @return string
     */
    function title( $attribute, $name = null )
    {
        $eztags = new eZTags();
        $eztags->createFromAttribute( $attribute );

        return $eztags->keywordString();
    }

    /**
     * Returns true if content object attribute has content
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @return bool
     */
    function hasObjectAttributeContent( $contentObjectAttribute )
    {
        $eztags = new eZTags();
        $eztags->createFromAttribute( $contentObjectAttribute );
        $idArray = $eztags->idArray();

        return !empty( $idArray );
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
        $eztags = new eZTags();
        $eztags->createFromAttribute( $contentObjectAttribute  );

        return $eztags->idString() . '|#' . $eztags->keywordString() . '|#' . $eztags->parentString();
    }

    /**
     * Creates the content object attribute content from the input string
     * Valid string value is list of ids, followed by list of keywords,
     * followed by list of parent ids, all together separated by '|#'
     * for example "1|#2|#3|#first tag|#second tag|#third tag|#12|#13|#14"
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @param string $string
     * @return bool
     */
    function fromString( $contentObjectAttribute, $string )
    {
        if ( trim( $string ) != '' )
        {
            $itemsArray = explode( '|#', trim( $string ) );
            if ( is_array( $itemsArray ) && !empty( $itemsArray ) && count( $itemsArray ) % 3 == 0 )
            {
                $tagsCount = count( $itemsArray ) / 3;
                $idArray = array_slice( $itemsArray, 0, $tagsCount );
                $keywordArray = array_slice( $itemsArray, $tagsCount, $tagsCount );
                $parentArray = array_slice( $itemsArray, $tagsCount * 2, $tagsCount );

                $idString = implode( '|#', $idArray );
                $keywordString = implode( '|#', $keywordArray );
                $parentString = implode( '|#', $parentArray );

                $eztags = new eZTags();
                $eztags->createFromStrings( $idString, $keywordString, $parentString );
                $contentObjectAttribute->setContent( $eztags );

                return true;
            }
        }

        return false;
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

        $eztags = new eZTags();
        $eztags->createFromAttribute( $objectAttribute );
        $dom = $node->ownerDocument;
        $idStringNode = $dom->createElement( 'id-string' );
        $idStringNode->appendChild( $dom->createTextNode( $eztags->idString() ) );
        $node->appendChild( $idStringNode );
        $keywordStringNode = $dom->createElement( 'keyword-string' );
        $keywordStringNode->appendChild( $dom->createTextNode( $eztags->keywordString() ) );
        $node->appendChild( $keywordStringNode );
        $parentStringNode = $dom->createElement( 'parent-string' );
        $parentStringNode->appendChild( $dom->createTextNode( $eztags->parentString() ) );
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
        $idString = $attributeNode->getElementsByTagName( 'id-string' )->item( 0 )->textContent;
        $keywordString = $attributeNode->getElementsByTagName( 'keyword-string' )->item( 0 )->textContent;
        $parentString = $attributeNode->getElementsByTagName( 'parent-string' )->item( 0 )->textContent;

        $eztags = new eZTags();
        $eztags->createFromStrings( $idString, $keywordString, $parentString );
        $objectAttribute->setContent( $eztags );
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
