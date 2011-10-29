<?php

class ezfSolrDocumentFieldeZTags extends ezfSolrDocumentFieldBase
{
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
