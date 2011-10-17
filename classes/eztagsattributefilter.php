<?php

/**
 * eZTagsAttributeFilter class implements TagsAttributeFilter extended attribute
 *
 */
class eZTagsAttributeFilter
{
    /**
     * Creates and returns SQL parts used in fetch functions
     *
     * @return array
     */
    function createSqlParts( $params )
    {
        $returnArray = array( 'tables' => '', 'joins'  => '', 'columns' => '' );

        if ( isset( $params['tag_id'] ) )
        {
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
                $result = eZTagsObject::fetchList( array( 'main_tag_id' => array( $tagIDsArray ) ), null, false );
                if ( is_array( $result ) && !empty( $result ) )
                {
                    foreach ( $result as $r )
                    {
                        array_push( $tagIDsArray, (int) $r['id'] );
                    }
                }
            }

            $returnArray['tables'] = ", eztags_attribute_link i1 ";

            $db = eZDB::instance();
            $dbString = $db->generateSQLINStatement( $tagIDsArray, 'i1.keyword_id', false, true, 'int' );

            $returnArray['joins'] = " $dbString AND i1.object_id = ezcontentobject.id AND i1.objectattribute_version = ezcontentobject.current_version AND ";
        }

        return $returnArray;
    }
}

?>
