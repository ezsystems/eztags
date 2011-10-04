<?php

/**
 * eZTagsKeyword class inherits eZPersistentObject class
 * to be able to access eztags_keyword database table through API
 *
 */
class eZTagsKeyword extends eZPersistentObject
{
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
                                                                              'required' => true ) ),
                      'function_attributes' => array( 'language_name' => 'languageName' ),
                      'keys'                => array( 'keyword_id', 'language_id' ),
                      'class_name'          => 'eZTagsKeyword',
                      'sort'                => array( 'keyword_id' => 'asc', 'language_id' => 'asc' ),
                      'name'                => 'eztags_keyword' );
    }

    /**
     * Returns eZTagsKeyword object for given tag ID and language ID
     *
     * @static
     * @param integer $tagID
     * @param integer $languageID
     * @return eZTagsKeyword
     */
    static function fetch( $tagID, $languageID )
    {
        return eZPersistentObject::fetchObject( self::definition(), null, array( 'keyword_id' => $tagID, 'language_id' => $languageID ) );
    }

    /**
     * Returns eZTagsKeyword object for given tag ID and language locale
     *
     * @static
     * @param integer $tagID
     * @param string $locale
     * @return eZTagsKeyword
     */
    static function fetchByLocale( $tagID, $locale )
    {
        return eZPersistentObject::fetchObject( self::definition(), null, array( 'keyword_id' => $tagID, 'locale' => $locale ) );
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
        $language = eZContentLanguage::fetch( $this->attribute( 'language_id' ) );

        if ( $language instanceof eZContentLanguage )
            return array( 'locale' => $language->attribute( 'locale' ), 'name' => $language->attribute( 'name' ) );

        return false;
    }
}

?>
