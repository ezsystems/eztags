<?php

/**
 * eZTagsKeyword class inherits eZPersistentObject class
 * to be able to access eztags_keyword database table through API
 */
class eZTagsKeyword extends eZPersistentObject
{
    /**
     * Defines the draft status of this object
     *
     * @const STATUS_DRAFT
     */
    const STATUS_DRAFT = 0;

    /**
     * Defines the published status of this object
     *
     * @const STATUS_PUBLISHED
     */
    const STATUS_PUBLISHED = 1;

    /**
     * Constructor
     *
     * @param array $row
     */
    function __construct( $row )
    {
        parent::eZPersistentObject( $row );
    }

    /**
     * Returns the definition array for eZTagsKeyword
     *
     * @return array
     */
    static public function definition()
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
     *
     * @param int $tagID
     * @param string $locale
     * @param bool $includeDrafts
     *
     * @return eZTagsKeyword
     */
    static public function fetch( $tagID, $locale, $includeDrafts = false )
    {
        $fetchParams = array( 'keyword_id' => $tagID, 'locale' => $locale );
        if ( !$includeDrafts )
            $fetchParams['status'] = self::STATUS_PUBLISHED;

        return parent::fetchObject( self::definition(), null, $fetchParams );
    }

    /**
     * Returns eZTagsKeyword list for given tag ID
     *
     * @static
     *
     * @param int $tagID
     *
     * @return eZTagsKeyword[]
     */
    static public function fetchByTagID( $tagID )
    {
        $tagKeywordList = parent::fetchObjectList( self::definition(), null, array( 'keyword_id' => $tagID ) );

        if ( is_array( $tagKeywordList ) )
            return $tagKeywordList;

        return array();
    }

    /**
     * Returns count of eZTagsKeyword objects for supplied tag ID
     *
     * @static
     *
     * @param int $tagID
     *
     * @return int
     */
    static public function fetchCountByTagID( $tagID )
    {
        return parent::count( self::definition(), array( 'keyword_id' => (int) $tagID ) );
    }

    /**
     * Returns array with language name and locale for this instance
     *
     * @return array
     */
    public function languageName()
    {
        /** @var eZContentLanguage $language */
        $language = eZContentLanguage::fetchByLocale( $this->attribute( 'locale' ) );

        if ( $language instanceof eZContentLanguage )
            return array( 'locale' => $language->attribute( 'locale' ), 'name' => $language->attribute( 'name' ) );

        return false;
    }
}
