<?php

/**
 * eZTagsType class implements the eztags datatype
 */
class eZTagsType extends eZDataType
{
    const DATA_TYPE_STRING = 'eztags';

    const SUBTREE_LIMIT_VARIABLE = '_eztags_subtree_limit_';
    const SUBTREE_LIMIT_FIELD = 'data_int1';

    const HIDE_ROOT_TAG_VARIABLE = '_eztags_hide_root_tag_';
    const HIDE_ROOT_TAG_FIELD = 'data_int3';

    const MAX_TAGS_VARIABLE = '_eztags_max_tags_';
    const MAX_TAGS_FIELD = 'data_int4';

    const EDIT_VIEW_VARIABLE = '_eztags_edit_view_';
    const EDIT_VIEW_FIELD = 'data_text1';
    const EDIT_VIEW_DEFAULT_VALUE = 'Default';

    /**
     * Constructor
     */
    function __construct()
    {
        parent::eZDataType( self::DATA_TYPE_STRING, ezpI18n::tr( 'extension/eztags/datatypes', 'Tags' ), array( 'serialize_supported' => true ) );
    }

    /**
     * Initializes the content class attribute
     *
     * @param eZContentClassAttribute $classAttribute
     */
    public function initializeClassAttribute( $classAttribute )
    {
        if ( $classAttribute->attribute( self::SUBTREE_LIMIT_FIELD ) === null )
            $classAttribute->setAttribute( self::SUBTREE_LIMIT_FIELD, 0 );

        if ( $classAttribute->attribute( self::HIDE_ROOT_TAG_FIELD ) === null )
            $classAttribute->setAttribute( self::HIDE_ROOT_TAG_FIELD, 0 );

        if ( $classAttribute->attribute( self::MAX_TAGS_FIELD ) === null )
            $classAttribute->setAttribute( self::MAX_TAGS_FIELD, 0 );

        if ( $classAttribute->attribute( self::EDIT_VIEW_FIELD ) === null )
            $classAttribute->setAttribute( self::EDIT_VIEW_FIELD, self::EDIT_VIEW_DEFAULT_VALUE );

        $classAttribute->store();
    }

    /**
     * Initializes content object attribute based on another attribute
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @param eZContentObjectVersion $currentVersion
     * @param eZContentObjectAttribute $originalContentObjectAttribute
     */
    public function initializeObjectAttribute( $contentObjectAttribute, $currentVersion, $originalContentObjectAttribute )
    {
        if ( $currentVersion != false )
        {
            $eZTags = eZTags::createFromAttribute( $originalContentObjectAttribute, $contentObjectAttribute->attribute( 'language_code' ) );
            $eZTags->store( $contentObjectAttribute );
        }
    }

    /**
     * Validates the data structure and returns true if it is valid for this datatype
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @param string $idString
     * @param string $keywordString
     * @param string $parentString
     * @param string $localeString
     *
     * @return bool
     */
    private function validateObjectAttribute( $contentObjectAttribute, $idString, $keywordString, $parentString, $localeString )
    {
        $classAttribute = $contentObjectAttribute->contentClassAttribute();

        // we cannot use empty() here as there can be cases where $parentString or $idString can be "0",
        // which evaluates to false with empty(), which is wrong for our use case
        if ( strlen( $keywordString ) == 0 && strlen( $parentString ) == 0 && strlen( $idString ) == 0 && strlen( $localeString ) == 0 )
        {
            if ( $contentObjectAttribute->validateIsRequired() )
            {
                $contentObjectAttribute->setValidationError( ezpI18n::tr( 'extension/eztags/datatypes', 'At least one tag is required to be added.' ) );
                return eZInputValidator::STATE_INVALID;
            }
        }
        // see comment above
        else if ( strlen( $keywordString ) == 0 || strlen( $parentString ) == 0 || strlen( $idString ) == 0 || strlen( $localeString ) == 0 )
        {
            $contentObjectAttribute->setValidationError( ezpI18n::tr( 'extension/eztags/datatypes', 'Attribute contains invalid data.' ) );
            return eZInputValidator::STATE_INVALID;
        }
        else
        {
            $idArray = explode( '|#', $idString );
            $keywordArray = explode( '|#', $keywordString );
            $parentArray = explode( '|#', $parentString );
            $localeArray = explode( '|#', $localeString );
            if ( count( $keywordArray ) != count( $idArray ) || count( $parentArray ) != count( $idArray ) || count( $localeArray ) != count( $idArray ) )
            {
                $contentObjectAttribute->setValidationError( ezpI18n::tr( 'extension/eztags/datatypes', 'Attribute contains invalid data.' ) );
                return eZInputValidator::STATE_INVALID;
            }

            $maxTags = (int) $classAttribute->attribute( self::MAX_TAGS_FIELD );
            if ( $maxTags > 0 && ( count( $idArray ) > $maxTags || count( $keywordArray ) > $maxTags || count( $parentArray ) > $maxTags || count( $localeArray ) > $maxTags ) )
            {
                $contentObjectAttribute->setValidationError( ezpI18n::tr( 'extension/eztags/datatypes', 'Up to %1 tags are allowed to be added.', null, array( '%1' => $maxTags ) ) );
                return eZInputValidator::STATE_INVALID;
            }
        }

        return eZInputValidator::STATE_ACCEPTED;
    }

    /**
     * Validates the input and returns true if the input was valid for this datatype
     *
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentObjectAttribute $contentObjectAttribute
     *
     * @return bool
     */
    public function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $contentObjectAttributeID = $contentObjectAttribute->attribute( 'id' );

        $keywordString = trim( $http->postVariable( $base . '_eztags_data_text_' . $contentObjectAttributeID, '' ) );
        $parentString = trim( $http->postVariable( $base . '_eztags_data_text2_' . $contentObjectAttributeID, '' ) );
        $idString = trim( $http->postVariable( $base . '_eztags_data_text3_' . $contentObjectAttributeID, '' ) );
        $localeString = trim( $http->postVariable( $base . '_eztags_data_text4_' . $contentObjectAttributeID, '' ) );

        return $this->validateObjectAttribute( $contentObjectAttribute, $idString, $keywordString, $parentString, $localeString );
    }

    /**
     * Fetches the HTTP POST input and stores it in the data instance
     *
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentObjectAttribute $contentObjectAttribute
     *
     * @return bool
     */
    public function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $contentObjectAttributeID = $contentObjectAttribute->attribute( 'id' );

        if ( !$http->hasPostVariable( $base . '_eztags_data_text_' . $contentObjectAttributeID ) )
            return false;

        if ( !$http->hasPostVariable( $base . '_eztags_data_text2_' . $contentObjectAttributeID ) )
            return false;

        if ( !$http->hasPostVariable( $base . '_eztags_data_text3_' . $contentObjectAttributeID ) )
            return false;

        if ( !$http->hasPostVariable( $base . '_eztags_data_text4_' . $contentObjectAttributeID ) )
            return false;

        $keywordString = trim( $http->postVariable( $base . '_eztags_data_text_' . $contentObjectAttributeID ) );
        $parentString = trim( $http->postVariable( $base . '_eztags_data_text2_' . $contentObjectAttributeID ) );
        $idString = trim( $http->postVariable( $base . '_eztags_data_text3_' . $contentObjectAttributeID ) );
        $localeString = trim( $http->postVariable( $base . '_eztags_data_text4_' . $contentObjectAttributeID ) );

        $eZTags = eZTags::createFromStrings( $contentObjectAttribute, $idString, $keywordString, $parentString, $localeString );
        $contentObjectAttribute->setContent( $eZTags );

        return true;
    }

    /**
     * Stores the object attribute
     *
     * @param eZContentObjectAttribute $attribute
     */
    public function storeObjectAttribute( $attribute )
    {
        /** @var $eZTags eZTags */
        $eZTags = $attribute->content();
        if ( $eZTags instanceof eZTags )
            $eZTags->store( $attribute );
    }

    /**
     * Validates class attribute HTTP input
     *
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentClassAttribute $attribute
     *
     * @return bool
     */
    public function validateClassAttributeHTTPInput( $http, $base, $attribute )
    {
        $classAttributeID = $attribute->attribute( 'id' );

        $maxTags = trim( $http->postVariable( $base . self::MAX_TAGS_VARIABLE . $classAttributeID, '' ) );
        if ( ( !is_numeric( $maxTags ) && !empty( $maxTags ) ) || (int) $maxTags < 0 )
            return eZInputValidator::STATE_INVALID;

        $subTreeLimit = (int) $http->postVariable( $base . self::SUBTREE_LIMIT_VARIABLE . $classAttributeID, -1 );
        if ( $subTreeLimit < 0 )
            return eZInputValidator::STATE_INVALID;

        if ( $subTreeLimit > 0 )
        {
            $tag = eZTagsObject::fetchWithMainTranslation( $subTreeLimit );
            if ( !$tag instanceof eZTagsObject || $tag->attribute( 'main_tag_id' ) > 0 )
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
     *
     * @return bool
     */
    public function fetchClassAttributeHTTPInput( $http, $base, $attribute )
    {
        $classAttributeID = $attribute->attribute( 'id' );

        $subTreeLimit = (int) $http->postVariable( $base . self::SUBTREE_LIMIT_VARIABLE . $classAttributeID, -1 );
        $maxTags = (int) trim( $http->postVariable( $base . self::MAX_TAGS_VARIABLE . $classAttributeID, -1 ) );

        if ( $subTreeLimit < 0 || $maxTags < 0 )
            return false;

        $hideRootTag = (int) $http->hasPostVariable( $base . self::HIDE_ROOT_TAG_VARIABLE . $classAttributeID );

        $editView = trim( $http->postVariable( $base . self::EDIT_VIEW_VARIABLE . $classAttributeID, self::EDIT_VIEW_DEFAULT_VALUE ) );

        $eZTagsIni = eZINI::instance( 'eztags.ini' );
        $availableEditViews = $eZTagsIni->variable( 'EditSettings', 'AvailableViews' );
        if ( !in_array( $editView, array_keys( $availableEditViews ) ) )
            return false;

        $attribute->setAttribute( self::SUBTREE_LIMIT_FIELD, $subTreeLimit );
        $attribute->setAttribute( self::HIDE_ROOT_TAG_FIELD, $hideRootTag );
        $attribute->setAttribute( self::MAX_TAGS_FIELD, $maxTags );
        $attribute->setAttribute( self::EDIT_VIEW_FIELD, $editView );

        return true;
    }

    /**
     * Extracts values from the attribute parameters and sets it in the class attribute.
     *
     * @param eZContentClassAttribute $classAttribute
     * @param DOMElement $attributeNode
     * @param DOMElement $attributeParametersNode
     */
    public function unserializeContentClassAttribute( $classAttribute, $attributeNode, $attributeParametersNode )
    {
        /** @var $domNodes DOMNodeList */
        $subTreeLimit = 0;
        $domNodes = $attributeParametersNode->getElementsByTagName( 'subtree-limit' );
        if ( $domNodes->length > 0 )
            $subTreeLimit = (int) $domNodes->item( 0 )->textContent;

        $maxTags = 0;
        $domNodes = $attributeParametersNode->getElementsByTagName( 'max-tags' );
        if ( $domNodes->length > 0 )
            $maxTags = (int) $domNodes->item( 0 )->textContent;

        $hideRootTag = 0;
        $domNodes = $attributeParametersNode->getElementsByTagName( 'hide-root-tag' );
        if ( $domNodes->length > 0 && $domNodes->item( 0 )->textContent === 'true' )
            $hideRootTag = 1;

        $editView = self::EDIT_VIEW_DEFAULT_VALUE;
        $domNodes = $attributeParametersNode->getElementsByTagName( 'edit-view' );
        if ( $domNodes->length > 0 )
        {
            $domNodeContent = trim( $domNodes->item( 0 )->textContent );
            if ( !empty( $domNodeContent ) )
            {
                $editView = $domNodeContent;
            }
        }

        $classAttribute->setAttribute( self::SUBTREE_LIMIT_FIELD, $subTreeLimit );
        $classAttribute->setAttribute( self::MAX_TAGS_FIELD, $maxTags );
        $classAttribute->setAttribute( self::HIDE_ROOT_TAG_FIELD, $hideRootTag );
        $classAttribute->setAttribute( self::EDIT_VIEW_FIELD, $editView );
    }

    /**
     * Adds the necessary DOM structure to the attribute parameters
     *
     * @param eZContentClassAttribute $classAttribute
     * @param DOMNode $attributeNode
     * @param DOMNode $attributeParametersNode
     */
    public function serializeContentClassAttribute( $classAttribute, $attributeNode, $attributeParametersNode )
    {
        $dom = $attributeParametersNode->ownerDocument;

        $subTreeLimit = (string) $classAttribute->attribute( self::SUBTREE_LIMIT_FIELD );
        $domNode = $dom->createElement( 'subtree-limit' );
        $domNode->appendChild( $dom->createTextNode( $subTreeLimit ) );
        $attributeParametersNode->appendChild( $domNode );

        $maxTags = (string) $classAttribute->attribute( self::MAX_TAGS_FIELD );
        $domNode = $dom->createElement( 'max-tags' );
        $domNode->appendChild( $dom->createTextNode( $maxTags ) );
        $attributeParametersNode->appendChild( $domNode );

        $hideRootTag = ( (int) $classAttribute->attribute( self::HIDE_ROOT_TAG_FIELD ) ) > 0 ? 'true' : 'false';
        $domNode = $dom->createElement( 'hide-root-tag' );
        $domNode->appendChild( $dom->createTextNode( $hideRootTag ) );
        $attributeParametersNode->appendChild( $domNode );

        $editView = (string) $classAttribute->attribute( self::EDIT_VIEW_FIELD );
        $domNode = $dom->createElement( 'edit-view' );
        $domNode->appendChild( $dom->createTextNode( $editView ) );
        $attributeParametersNode->appendChild( $domNode );
    }

    /**
     * Returns the content
     *
     * @param eZContentObjectAttribute $attribute
     *
     * @return eZTags
     */
    public function objectAttributeContent( $attribute )
    {
        return eZTags::createFromAttribute( $attribute );
    }

    /**
     * Returns the meta data used for storing search indices
     *
     * @param eZContentObjectAttribute $attribute
     *
     * @return string
     */
    public function metaData( $attribute )
    {
        /** @var $eZTags eZTags */
        $eZTags = $attribute->content();
        if ( !$eZTags instanceof eZTags )
            return '';

        $indexSynonyms = eZINI::instance( 'eztags.ini' )->variable( 'SearchSettings', 'IndexSynonyms' ) === 'enabled';

        $keywords = array();
        $tags = $eZTags->attribute( 'tags' );

        /** @var eZTagsObject $tag */
        foreach ( $tags as $tag )
        {
            if ( !$indexSynonyms && $tag->isSynonym() )
                $tag = $tag->getMainTag();

            if ( $tag instanceof eZTagsObject )
            {
                $keyword = $tag->getKeyword( $attribute->attribute( 'language_code' ) );
                if ( !$keyword )
                {
                    //fall back to main language
                    /** @var eZContentLanguage $mainLanguage */
                    $mainLanguage = eZContentLanguage::fetch( $tag->attribute( 'main_language_id') );
                    if ( $mainLanguage instanceof eZContentLanguage )
                        $keyword = $tag->getKeyword( $mainLanguage->attribute( 'locale' ) );
                }
                if ( $keyword )
                    $keywords[] = $keyword;
            }
        }

        return implode( ', ', array_unique( $keywords ) );
    }

    /**
     * Delete stored object attribute
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @param eZContentObjectVersion $version
     */
    public function deleteStoredObjectAttribute( $contentObjectAttribute, $version = null )
    {
        $contentObjectAttributeID = $contentObjectAttribute->attribute( 'id' );
        eZTagsAttributeLinkObject::removeByAttribute( $contentObjectAttributeID, $version );
    }

    /**
     * Returns the content of eztags attribute for use as a title
     *
     * @param eZContentObjectAttribute $attribute
     * @param string $name
     *
     * @return string
     */
    public function title( $attribute, $name = null )
    {
        return $this->metaData( $attribute );
    }

    /**
     * Returns true if content object attribute has content
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     *
     * @return bool
     */
    public function hasObjectAttributeContent( $contentObjectAttribute )
    {
        /** @var $eZTags eZTags */
        $eZTags = $contentObjectAttribute->content();
        if ( !$eZTags instanceof eZTags )
            return false;

        $tagsCount = $eZTags->attribute( 'tags_count' );
        return $tagsCount > 0;
    }

    /**
     * Returns if the content is indexable
     *
     * @return bool
     */
    public function isIndexable()
    {
        return true;
    }

    /**
     * Returns string representation of a content object attribute
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     *
     * @return string
     */
    public function toString( $contentObjectAttribute )
    {
        /** @var $eZTags eZTags */
        $eZTags = $contentObjectAttribute->content();
        if ( !$eZTags instanceof eZTags )
            return '';

        $returnArray = array();
        $returnArray[] = $eZTags->attribute( 'id_string' );
        $returnArray[] = $eZTags->attribute( 'keyword_string' );
        $returnArray[] = $eZTags->attribute( 'parent_string' );
        $returnArray[] = $eZTags->attribute( 'locale_string' );

        return implode( '|#', $returnArray );
    }

    /**
     * Creates the content object attribute content from the input string
     * Valid string value is list of ids, followed by list of keywords,
     * followed by list of parent ids, followed by list of locales
     * all together separated by '|#'
     *
     * for example "1|#2|#3|#first tag|#second tag|#third tag|#12|#13|#14|#eng-GB|#eng-GB|#eng-GB"
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @param string $string
     *
     * @return bool
     */
    public function fromString( $contentObjectAttribute, $string )
    {
        $idString = '';
        $keywordString = '';
        $parentString = '';
        $localeString = '';

        $string = trim( $string );
        if ( !empty( $string ) )
        {
            $itemsArray = explode( '|#', $string );
            if ( !is_array( $itemsArray ) || empty( $itemsArray ) || count( $itemsArray ) % 4 != 0 )
                return false;

            $tagsCount = count( $itemsArray ) / 4;
            $idArray = array_slice( $itemsArray, 0, $tagsCount );
            $keywordArray = array_slice( $itemsArray, $tagsCount, $tagsCount );
            $parentArray = array_slice( $itemsArray, $tagsCount * 2, $tagsCount );
            $localeArray = array_slice( $itemsArray, $tagsCount * 3, $tagsCount );

            $idString = implode( '|#', $idArray );
            $keywordString = implode( '|#', $keywordArray );
            $parentString = implode( '|#', $parentArray );
            $localeString = implode( '|#', $localeArray );
        }

        $validationResult = $this->validateObjectAttribute( $contentObjectAttribute, $idString, $keywordString, $parentString, $localeString );
        if ( $validationResult != eZInputValidator::STATE_ACCEPTED )
            return false;

        $eZTags = eZTags::createFromStrings( $contentObjectAttribute, $idString, $keywordString, $parentString, $localeString );
        $contentObjectAttribute->setContent( $eZTags );

        return true;
    }

    /**
     * Serializes the content object attribute
     *
     * @param eZPackage $package
     * @param eZContentObjectAttribute $objectAttribute
     *
     * @return DOMNode
     */
    public function serializeContentObjectAttribute( $package, $objectAttribute )
    {
        $node = $this->createContentObjectAttributeDOMNode( $objectAttribute );

        /** @var $eZTags eZTags */
        $eZTags = $objectAttribute->content();
        if ( !$eZTags instanceof eZTags )
            return $node;

        $dom = $node->ownerDocument;

        $idStringNode = $dom->createElement( 'id-string' );
        $idStringNode->appendChild( $dom->createTextNode( $eZTags->attribute( 'id_string' ) ) );
        $node->appendChild( $idStringNode );

        $keywordStringNode = $dom->createElement( 'keyword-string' );
        $keywordStringNode->appendChild( $dom->createTextNode( $eZTags->attribute( 'keyword_string' ) ) );
        $node->appendChild( $keywordStringNode );

        $parentStringNode = $dom->createElement( 'parent-string' );
        $parentStringNode->appendChild( $dom->createTextNode( $eZTags->attribute( 'parent_string' ) ) );
        $node->appendChild( $parentStringNode );

        $localeStringNode = $dom->createElement( 'locale-string' );
        $localeStringNode->appendChild( $dom->createTextNode( $eZTags->attribute( 'locale_string' ) ) );
        $node->appendChild( $localeStringNode );

        return $node;
    }

    /**
     * Unserializes the content object attribute from provided DOM node
     *
     * @param eZPackage $package
     * @param eZContentObjectAttribute $objectAttribute
     * @param DOMElement $attributeNode
     */
    public function unserializeContentObjectAttribute( $package, $objectAttribute, $attributeNode )
    {
        $idString = $attributeNode->getElementsByTagName( 'id-string' )->item( 0 )->textContent;
        $keywordString = $attributeNode->getElementsByTagName( 'keyword-string' )->item( 0 )->textContent;
        $parentString = $attributeNode->getElementsByTagName( 'parent-string' )->item( 0 )->textContent;
        $localeString = $attributeNode->getElementsByTagName( 'locale-string' )->item( 0 )->textContent;

        $validationResult = $this->validateObjectAttribute( $objectAttribute, $idString, $keywordString, $parentString, $localeString );
        if ( $validationResult == eZInputValidator::STATE_ACCEPTED )
        {
            $eZTags = eZTags::createFromStrings( $objectAttribute, $idString, $keywordString, $parentString, $localeString );
            $objectAttribute->setContent( $eZTags );
        }
    }

    /**
     * Returns if the content supports batch initialization
     *
     * @return bool
     */
    public function supportsBatchInitializeObjectAttribute()
    {
        return true;
    }

    /**
     * Sets grouped_input to true for edit view of the datatype
     *
     * @param eZContentObjectAttribute $objectAttribute
     * @param array|bool $mergeInfo
     *
     * @return array
     */
    public function objectDisplayInformation( $objectAttribute, $mergeInfo = false )
    {
        return eZDataType::objectDisplayInformation(
            $objectAttribute,
            array( 'edit' => array( 'grouped_input' => true ) )
        );
    }
}

eZDataType::register( eZTagsType::DATA_TYPE_STRING, 'eZTagsType' );
