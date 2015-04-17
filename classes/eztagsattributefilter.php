<?php

/**
 * eZTagsAttributeFilter class implements TagsAttributeFilter extended attribute
 */
class eZTagsAttributeFilter
{
    /**
     * Creates and returns SQL parts used in fetch functions
     *
     * @param array $params
     *
     * @return array
     */
    public function createSqlParts( $params )
    {
        $returnArray = array( 'tables' => '', 'joins'  => '', 'columns' => '' );

        if ( !isset( $params['tag_id'] ) )
            return $returnArray;

        if ( is_array( $params['tag_id'] ) )
        {
            $tagIDsArray = $params['tag_id'];
        }
        else if ( (int) $params['tag_id'] > 0 )
        {
            $tagIDsArray = array( (int) $params['tag_id'] );
        }
        else
        {
            return $returnArray;
        }

        if ( !isset( $params['include_synonyms'] ) || ( isset( $params['include_synonyms'] ) && (bool) $params['include_synonyms'] == true ) )
        {
            /** @var eZTagsObject[] $tags */
            $tags = eZTagsObject::fetchList( array( 'main_tag_id' => array( $tagIDsArray ) ) );
            if ( is_array( $tags ) )
            {
                foreach ( $tags as $tag )
                {
                    $tagIDsArray[] = $tag->attribute( 'id' );
                }
            }
        }

        $returnArray['tables'] = " INNER JOIN eztags_attribute_link i1 ON (i1.object_id = ezcontentobject.id AND i1.objectattribute_version = ezcontentobject.current_version)
                                   INNER JOIN eztags i2 ON (i1.keyword_id = i2.id)
                                   INNER JOIN eztags_keyword i3 ON (i2.id = i3.keyword_id)";

        $db = eZDB::instance();
        $dbString = $db->generateSQLINStatement( $tagIDsArray, 'i1.keyword_id', false, true, 'int' );

        if ( isset( $params['language'] ) )
        {
            $language = $params['language'];
            if ( !is_array( $language ) )
                $language = array( $language );

            eZContentLanguage::setPrioritizedLanguages( $language );
        }

        $returnArray['joins'] = " $dbString
                                  AND " . eZContentLanguage::languagesSQLFilter( 'i2' ) . " AND " .
                                  eZContentLanguage::sqlFilter( 'i3', 'i2' ) . " AND ";

        if ( isset( $params['language'] ) )
            eZContentLanguage::clearPrioritizedLanguages();

        return $returnArray;
    }

    /**
     * Creates and returns SQL parts used in fetch functions
     *
     * @param array $params
     *
     * @return array
     */
    public function createAndFilterSqlParts( $params )
    {
        $returnArray = array( 'tables' => '', 'joins'  => '', 'columns' => '' );

        if ( !isset( $params['tag_id'] ) )
            return $returnArray;

        if ( is_array( $params['tag_id'] ) )
        {
            $tagIDsArray = $params['tag_id'];
        }
        else if ( (int) $params['tag_id'] > 0 )
        {
            $tagIDsArray = array( (int) $params['tag_id'] );
        }
        else
        {
            return $returnArray;
        }

        if ( !isset( $params['include_synonyms'] ) || ( isset( $params['include_synonyms'] ) && (bool) $params['include_synonyms'] == true ) )
        {
            /** @var eZTagsObject[] $tags */
            $tags = eZTagsObject::fetchList( array( 'main_tag_id' => array( $tagIDsArray ) ) );
            if ( is_array( $tags ) )
            {
                foreach ( $tags as $tag )
                {
                    $tagIDsArray[] = $tag->attribute( 'id' );
                }
            }
        }

        $returnArray['tables'] = " INNER JOIN eztags_attribute_link i1 ON (i1.object_id = ezcontentobject.id AND i1.objectattribute_version = ezcontentobject.current_version)
                                   INNER JOIN eztags i2 ON (i1.keyword_id = i2.id)
                                   INNER JOIN eztags_keyword i3 ON (i2.id = i3.keyword_id)";

        $dbStrings = array();
        foreach ( $tagIDsArray as $tagID )
        {
            if ( is_numeric( $tagID ) )
            {
                $dbStrings[] = "EXISTS (
                    SELECT 1
                    FROM
                        eztags_attribute_link j1,
                        ezcontentobject j2
                    WHERE j1.keyword_id = " . (int) $tagID .
                    " AND j1.object_id = j2.id
                    AND j2.id = ezcontentobject.id
                    AND j1.objectattribute_version = j2.current_version
                )";
            }
        }

        $dbString = implode( " AND ", $dbStrings );

        if ( isset( $params['language'] ) )
        {
            $language = $params['language'];
            if ( !is_array( $language ) )
                $language = array( $language );

            eZContentLanguage::setPrioritizedLanguages( $language );
        }

        $returnArray['joins'] = " $dbString
                                  AND " . eZContentLanguage::languagesSQLFilter( 'i2' ) . " AND " .
                                  eZContentLanguage::sqlFilter( 'i3', 'i2' ) . " AND ";

        if ( isset( $params['language'] ) )
            eZContentLanguage::clearPrioritizedLanguages();

        return $returnArray;
    }

    /**
     * Creates and returns SQL parts used in fetch functions
     *
     * @param array $params where 'tag_id' is an array of arrays; at least one tag ID from each array must match
     *
     * @return array
     */
    public function createAndMultipleFilterSqlParts( $params )
    {
        $returnArray = array( 'tables' => '', 'joins'  => '', 'columns' => '' );

        if ( !isset( $params['tag_id'] ) )
            return $returnArray;

        if ( is_array( $params['tag_id'] ) )
        {
            $tagIDsArray = $params['tag_id'];
        }
        else
        {
            return $returnArray;
        }

        $returnArray['tables'] = " INNER JOIN eztags_attribute_link i1 ON (i1.object_id = ezcontentobject.id AND i1.objectattribute_version = ezcontentobject.current_version)
                                   INNER JOIN eztags i2 ON (i1.keyword_id = i2.id)
                                   INNER JOIN eztags_keyword i3 ON (i2.id = i3.keyword_id)";

        $dbStrings = array();
        $db = eZDB::instance();
        foreach ( $tagIDsArray as $tagIDGroup )
        {
            $tagIDGroup = (array) $tagIDGroup;
            if ( !isset( $params['include_synonyms'] ) || ( isset( $params['include_synonyms'] ) && (bool) $params['include_synonyms'] == true ) )
            {
                /** @var eZTagsObject[] $tags */
                $tags = eZTagsObject::fetchList( array( 'main_tag_id' => array( $tagIDGroup ) ) );
                if ( is_array( $tags ) )
                {
                    foreach ( $tags as $tag )
                    {
                        $tagIDGroup[] = $tag->attribute( 'id' );
                    }
                }
            }
            $dbStrings[] = "EXISTS (
                SELECT 1
                FROM
                    eztags_attribute_link j1,
                    ezcontentobject j2
                WHERE " . $db->generateSQLINStatement( $tagIDGroup, 'j1.keyword_id', false, true, 'int' ) .
                " AND j1.object_id = j2.id
                AND j2.id = ezcontentobject.id
                AND j1.objectattribute_version = j2.current_version
            )";
        }

        $dbString = implode( " AND ", $dbStrings );

        if ( isset( $params['language'] ) )
        {
            $language = $params['language'];
            if ( !is_array( $language ) )
                $language = array( $language );

            eZContentLanguage::setPrioritizedLanguages( $language );
        }

        $returnArray['joins'] = " $dbString
                                  AND " . eZContentLanguage::languagesSQLFilter( 'i2' ) . " AND " .
                                  eZContentLanguage::sqlFilter( 'i3', 'i2' ) . " AND ";

        if ( isset( $params['language'] ) )
            eZContentLanguage::clearPrioritizedLanguages();

        return $returnArray;
    }
}
