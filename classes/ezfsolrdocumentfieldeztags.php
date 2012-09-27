<?php

/**
 * ezfSolrDocumentFieldeZTags class implements custom indexing
 * handler for eZ Find
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

        /** @var eZContentClassAttribute $contentClassAttribute */
        $contentObjectAttribute = $this->ContentObjectAttribute;
        $contentClassAttribute = $contentObjectAttribute->contentClassAttribute();

        $keywordFieldName = parent::generateAttributeFieldName( $contentClassAttribute, 'lckeyword' );
        $textFieldName = parent::generateAttributeFieldName( $contentClassAttribute, 'text' );
        $tagIDsFieldName = parent::generateSubattributeFieldName( $contentClassAttribute, 'tag_ids', 'sint' );

        $data[$keywordFieldName] = '';
        $data[$textFieldName] = '';
        $data[$tagIDsFieldName] = array();

        if ( !$contentObjectAttribute->hasContent() )
            return $data;

        /** @var eZTags $objectAttributeContent */
        $objectAttributeContent = $contentObjectAttribute->content();

        $tagIDs = array();
        $keywords = array();
        $indexSynonyms = eZINI::instance( 'eztags.ini' )->variable( 'SearchSettings', 'IndexSynonyms' ) === 'enabled';

        $tags = $objectAttributeContent->attribute( 'tags' );
        if ( is_array( $tags ) )
        {
            /** @var eZTagsObject $tag */
            foreach ( $tags as $tag )
            {
                if ( !$indexSynonyms && $tag->isSynonym() )
                    $tag = $tag->getMainTag();

                if ( $tag instanceof eZTagsObject )
                {
                    //get keyword in content's locale
                    $keyword = $tag->getKeyword( $contentObjectAttribute->attribute( 'language_code' ) );
                    if ( !$keyword )
                    {
                        //fall back to main language
                        /** @var eZContentLanguage $mainLanguage */
                        $mainLanguage = eZContentLanguage::fetch( $tag->attribute( 'main_language_id') );
                        if ( $mainLanguage instanceof eZContentLanguage )
                            $keyword = $tag->getKeyword( $mainLanguage->attribute( 'locale' ) );
                    }

                    if ( $keyword )
                    {
                        $tagIDs[] = (int) $tag->attribute( 'id' );
                        $keywords[] = $keyword;
                    }
                }
            }
        }

        if ( !empty( $tagIDs ) )
        {
            $data[$keywordFieldName] = implode( ', ', $keywords );
            $data[$textFieldName] = implode( ' ', $keywords );
            $data[$tagIDsFieldName] = $tagIDs;
        }

        $data['ezf_df_tag_ids'] = $tagIDs;
        $data['ezf_df_tags'] = $keywords;

        return $data;
    }

    /**
     * Returns the list of field names this handler sends to Solr backend
     *
     * @static
     *
     * @param eZContentClassAttribute $classAttribute
     * @param array $exclusiveTypeFilter
     *
     * @return array
     */
    static public function getFieldNameList( eZContentClassAttribute $classAttribute, $exclusiveTypeFilter = array() )
    {
        $fieldsList = array();

        if ( empty( $exclusiveTypeFilter ) || !in_array( 'lckeyword', $exclusiveTypeFilter ) )
            $fieldsList[] = parent::generateAttributeFieldName( $classAttribute, 'lckeyword' );

        if ( empty( $exclusiveTypeFilter ) || !in_array( 'text', $exclusiveTypeFilter ) )
            $fieldsList[] = parent::generateAttributeFieldName( $classAttribute, 'text' );

        if ( empty( $exclusiveTypeFilter ) || !in_array( 'sint', $exclusiveTypeFilter ) )
            $fieldsList[] = parent::generateSubattributeFieldName( $classAttribute, 'tag_ids', 'sint' );

        return $fieldsList;
    }
}
