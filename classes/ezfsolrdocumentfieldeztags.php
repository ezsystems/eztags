<?php

/**
 * ezfSolrDocumentFieldeZTags class implements custom indexing
 * handler for eZ Find
 *
 */
class ezfSolrDocumentFieldeZTags extends ezfSolrDocumentFieldBase
{
    /**
     * Returns the data from content object attribute which is sent to Solr backend
     *
     * @return array
     */
    public function getData()
    {
        $data = array();

        $contentClassAttribute = $this->ContentObjectAttribute->contentClassAttribute();

        $keywordFieldName = parent::generateAttributeFieldName( $contentClassAttribute, 'lckeyword' );
        $textFieldName = parent::generateAttributeFieldName( $contentClassAttribute, 'text' );

        $data[$keywordFieldName] = '';
        $data[$textFieldName] = '';

        if ( $this->ContentObjectAttribute->hasContent() )
        {
            $keywordString = $this->ContentObjectAttribute->content()->keywordString( ', ' );
            $textString = $this->ContentObjectAttribute->content()->keywordString( ' ' );

            $data[$keywordFieldName] = $keywordString;
            $data[$textFieldName] = $textString;
        }

        return $data;
    }

    /**
     * Returns the list of field names this handler sends to Solr backend
     *
     * @static
     * @param eZContentClassAttribute $classAttribute
     * @param array $exclusiveTypeFilter
     * @return array
     */
    public static function getFieldNameList( eZContentClassAttribute $classAttribute, $exclusiveTypeFilter = array() )
    {
        $fieldsList = array();

        if ( empty( $exclusiveTypeFilter ) || !in_array( 'lckeyword', $exclusiveTypeFilter ) )
            $fieldsList[] = parent::generateAttributeFieldName( $classAttribute, 'lckeyword' );

        if ( empty( $exclusiveTypeFilter ) || !in_array( 'text', $exclusiveTypeFilter ) )
            $fieldsList[] = parent::generateAttributeFieldName( $classAttribute, 'text' );

        return $fieldsList;
    }
}

?>
