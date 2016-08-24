<?php

/**
 * eZTagsTreeAttributeFilter class implements TagsTreeAttributeFilter extended attribute
 */
class eZTagsTreeAttributeFilter
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

        if ( !isset( $params['parent_tag_id'] ) )
        {
            return $returnArray;
        }

        if ( is_array( $params['parent_tag_id'] ) )
        {
            $parentTagIDsArray = $params['parent_tag_id'];
        }
        else if ( (int) $params['parent_tag_id'] > 0 )
        {
            $parentTagIDsArray = array( (int) $params['parent_tag_id'] );
        }
        else
        {
            return $returnArray;
        }

        $suffix = '/%';
        if ( isset( $params['exclude_parent'] ) && $params['exclude_parent'] )
        {
            $suffix = '/%/%';
        }

        $returnArray['tables'] = " INNER JOIN eztags_attribute_link i1 ON (i1.object_id = ezcontentobject.id AND i1.objectattribute_version = ezcontentobject.current_version)
                                   INNER JOIN eztags i2 ON (i1.keyword_id = i2.id)
                                   INNER JOIN eztags_keyword i3 ON (i2.id = i3.keyword_id)";

        $db = eZDB::instance();
        $dbStrings = array();

        if ( isset( $params['type'] ) && strtolower( $params['type'] ) == 'and' )
        {
            foreach ( $parentTagIDsArray as $parentTagID )
            {
                $dbStrings[] = "EXISTS (
                    SELECT 1
                    FROM
                        eztags_attribute_link j1,
                        ezcontentobject j2,
                        eztags j3
                    WHERE j3.path_string LIKE \"%/" . $db->escapeString( $parentTagID . $suffix ) . "
                    AND j1.object_id = j2.id
                    AND j2.id = ezcontentobject.id
                    AND j1.objectattribute_version = j2.current_version
                    AND j3.id = j1.keyword_id
                )";
            }

            $dbString = implode( ' AND ', $dbStrings );
        }
        else
        {
            foreach ( $parentTagIDsArray as $parentTagID )
            {
                $dbStrings[] = ' i2.path_string LIKE "%/' . $db->escapeString( $parentTagID . $suffix ) . '"';
            }

            $dbString = implode( ' OR ', $dbStrings );
        }

        if ( isset( $params['language'] ) )
        {
            $language = $params['language'];
            if ( !is_array( $language ) )
            {
                $language = array( $language );
            }

            eZContentLanguage::setPrioritizedLanguages( $language );
        }


        $returnArray['joins'] = " ( $dbString )
                                  AND " . eZContentLanguage::languagesSQLFilter( 'i2' ) . " AND " .
                                  eZContentLanguage::sqlFilter( 'i3', 'i2' ) . " AND ";

        if ( isset( $params['language'] ) )
        {
            eZContentLanguage::clearPrioritizedLanguages();
        }

        return $returnArray;
    }
}
