<?php

/**
 * eZTagsKeyword class inherits eZPersistentObject class
 * to be able to access eztags_keyword database table through API
 *
 */
class eZTagsKeyword extends eZPersistentObject
{
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;

    /**
     * Constructor
     *
     */
    function __construct( $row )
    {
        parent::__construct( $row );
    }

    /**
     * Returns the definition array for eZTagsKeyword
     *
     * @return array
     */
    static function definition()
    {
        return array( 'fields'              => array( 'keyword_id'  => array( 'name'     => 'KeywordID',
                                                                              'datatype' => 'integer',
                                                                              'default'  => 0,
                                                                              'required' => true ),
                                                      'language_id' => array( 'name'     => 'LanguageID',
                                                                              'datatype' => 'integer',
                                                                              'default'  => 0,
                                                                              'required' => true ),
                                                      'keyword'     => array( 'name'     => 'Keyword',
                                                                              'datatype' => 'string',
                                                                              'default'  => '',
                                                                              'required' => true ),
                                                      'locale'      => array( 'name'     => 'Locale',
                                                                              'datatype' => 'string',
                                                                              'default'  => '',
                                                                              'required' => true ),
                                                      'status'      => array( 'name'     => 'Status',
                                                                              'datatype' => 'integer',
                                                                              'default'  => self::STATUS_DRAFT,
                                                                              'required' => true ) ),
                      'function_attributes' => array( 'language_name' => 'languageName' ),
                      'keys'                => array( 'keyword_id', 'locale' ),
                      'class_name'          => 'eZTagsKeyword',
                      'sort'                => array( 'keyword_id' => 'asc', 'locale' => 'asc' ),
                      'name'                => 'eztags_keyword' );
    }

    /**
     * Returns eZTagsKeyword object for given tag ID and locale
     *
     * @static
     * @param integer $tagID
     * @param integer $locale
     * @param bool $includeDrafts
     * @return eZTagsKeyword
     */
    static function fetch( $tagID, $locale, $includeDrafts = false )
    {
        $fetchParams = array( 'keyword_id' => $tagID, 'locale' => $locale );
        if ( !$includeDrafts )
            $fetchParams['status'] = self::STATUS_PUBLISHED;

        return eZPersistentObject::fetchObject( self::definition(), null, $fetchParams );
    }

    /**
     * Returns eZTagsKeyword list for given tag ID
     *
     * @static
     * @param integer $tagID
     * @return array
     */
    static function fetchByTagID( $tagID )
    {
        $tagKeywordList = eZPersistentObject::fetchObjectList( self::definition(), null, array( 'keyword_id' => $tagID ) );

        if ( is_array( $tagKeywordList ) )
            return $tagKeywordList;

        return array();
    }

    static function fetchCountByTagID( $tagID )
    {
        return eZPersistentObject::count( self::definition(), array( 'keyword_id' => (int) $tagID ) );
    }

    function languageName()
    {
        $language = eZContentLanguage::fetchByLocale( $this->attribute( 'locale' ) );

        if ( $language instanceof eZContentLanguage )
            return array( 'locale' => $language->attribute( 'locale' ), 'name' => $language->attribute( 'name' ) );

        return false;
    }
}

?>
