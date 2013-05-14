<?php

/**
 * ezfSolrDocumentFieldeZTags class implements custom indexing
 * handler for eZ Find
 */
class ezfSolrDocumentFieldeZTags extends ezfSolrDocumentFieldBase
{
    /**
     * If true, synonyms will be indexed, otherwise, main tag will be indexed instead
     *
     * @var bool
     */
    private $indexSynonyms = true;

    /**
     * Returns the data from content object attribute which is sent to Solr backend
     *
     * @return array
     */
    public function getData()
    {
        $data = array();

        /** @var eZContentClassAttribute $contentClassAttribute */
        $contentClassAttribute = $this->ContentObjectAttribute->contentClassAttribute();

        $keywordFieldName = parent::generateAttributeFieldName( $contentClassAttribute, 'lckeyword' );
        $textFieldName = parent::generateAttributeFieldName( $contentClassAttribute, 'text' );
        $tagIDsFieldName = parent::generateSubattributeFieldName( $contentClassAttribute, 'tag_ids', 'sint' );

        $data[$keywordFieldName] = '';
        $data[$textFieldName] = '';
        $data[$tagIDsFieldName] = array();

        if ( !$this->ContentObjectAttribute->hasContent() )
            return $data;

        $objectAttributeContent = $this->ContentObjectAttribute->content();
        if ( !$objectAttributeContent instanceof eZTags )
            return $data;

        $tagIDs = array();
        $keywords = array();

        $this->indexSynonyms = eZINI::instance( 'eztags.ini' )->variable( 'SearchSettings', 'IndexSynonyms' ) === 'enabled';
        $indexParentTags = eZINI::instance( 'eztags.ini' )->variable( 'SearchSettings', 'IndexParentTags' ) === 'enabled';
        $includeSynonyms = eZINI::instance( 'eztags.ini' )->variable( 'SearchSettings', 'IncludeSynonyms' ) === 'enabled';

        $tags = $objectAttributeContent->attribute( 'tags' );
        if ( is_array( $tags ) )
        {
            foreach ( $tags as $tag )
            {
                if ( $tag instanceof eZTagsObject )
                {
                    $this->processTag( $tag, $tagIDs, $keywords, $indexParentTags, $includeSynonyms );
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

    /**
     * Extracts data to be indexed from the tag
     *
     * @param eZTagsObject $tag
     * @param array $tagIDs
     * @param array $keywords
     * @param bool $indexParentTags
     * @param bool $includeSynonyms
     */
    private function processTag( eZTagsObject $tag, array &$tagIDs, array &$keywords, $indexParentTags = false, $includeSynonyms = false )
    {
        if ( !$this->indexSynonyms && $tag->isSynonym() )
            $tag = $tag->getMainTag();

        //get keyword in content's locale
        $keyword = $tag->getKeyword( $this->ContentObjectAttribute->attribute( 'language_code' ) );
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

        if ( $indexParentTags )
        {
            $parentTags = $tag->getPath( true );
            foreach ( $parentTags as $parentTag )
            {
                if ( $parentTag instanceof eZTagsObject )
                {
                    $this->processTag( $parentTag, $tagIDs, $keywords );
                }
            }
        }

        if ( $this->indexSynonyms && $includeSynonyms )
        {
            foreach ( $tag->getSynonyms() as $synonym )
            {
                if ( $synonym instanceof eZTagsObject )
                {
                    $this->processTag( $synonym, $tagIDs, $keywords );
                }
            }
        }
    }
}
