<?php

/**
 * eZTags class implements functions used by eztags datatype
 * 
 */
class eZTags
{
    
    /// Contains the keywords
    private $KeywordArray = array();

    /// Contains parent IDs in same order as keywords
    private $ParentArray = array();

    /// Contains the ID attribute if fetched
    private $ObjectAttributeID = false;
    
    function attributes()
    {
        return array( 'keywords',
                      'keyword_string',
                      'parent_string',
                      'related_objects',
                      'related_nodes' );
    }

    function hasAttribute( $name )
    {
        return in_array( $name, $this->attributes() );
    }

    function attribute( $name )
    {
        switch ( $name )
        {
            case 'keywords' :
            {
                return $this->KeywordArray;
            }break;

            case 'keyword_string' :
            {
                return $this->keywordString();
            }break;

            case 'parent_string' :
            {
                return $this->parentString();
            }break;

            case 'related_objects' :
            case 'related_nodes' :
            {
                return $this->relatedObjects();
            }break;
            default:
            {
                eZDebug::writeError( "Attribute '$name' does not exist", 'eZTags::attribute' );
                return null;
            }break;
        }
    }

    /*!
     Initialze the keyword index
    */
    function initializeKeyword( $keywordString, $parentString )
    {
        $keywordArray = explode( ',', $keywordString );
        $parentArray = explode( ',', $parentString );
        $wordArray = array();
        foreach ( array_keys( $keywordArray ) as $key )
        {
            $wordArray[] = trim( $keywordArray[$key] ).",".trim($parentArray[$key]);
        }
        $wordArray = array_unique( $wordArray );
        foreach ( $wordArray as $wordKey )
        {
            $word = explode( ',', $wordKey );
            if ($word[0] != '') {
                $this->KeywordArray[] = $word[0];
                $this->ParentArray[] = $word[1];
            }
        }
    }

    /*!
     Stores the keyword index to database
    */
    function store( $attribute )
    {
        $db = eZDB::instance();

        $object = $attribute->attribute( 'object' );

        // Get already existing keywords
        if ( count( $this->KeywordArray ) > 0 )
        {
            $escapedKeywordArray = array();
            foreach( $this->KeywordArray as $keyword )
            {
                $keyword = $db->escapeString( $keyword );
                $escapedKeywordArray[] = '\'' . $keyword . '\'';
            }
            $wordsString = $db->generateSQLINStatement( $escapedKeywordArray, 'keyword', false, true, 'string' );
            $existingWords = $db->arrayQuery( "SELECT * FROM eztags WHERE $wordsString" );
        }
        else
        {
            $existingWords = array();
        }

        $newWordArray = array();
        $existingWordArray = array();
        // Find out which words to store
        foreach ( array_keys( $this->KeywordArray ) as $key )
        {
            $wordExists = false;
            $wordID = false;
            foreach ( $existingWords as $existingKeyword )
            {
                if ( $this->KeywordArray[$key] == $existingKeyword['keyword'] )
                {
                     $wordExists = true;
                     $wordID = $existingKeyword['id'];
                     break;
                }
            }

            if ( $wordExists == false )
            {
                $newWordArray[] = array( 'keyword' => $this->KeywordArray[$key], 'parent_id' => $this->ParentArray[$key] );
            }
            else
            {
                $existingWordArray[] = array( 'keyword' => $this->KeywordArray[$key], 'parent_id' => $this->ParentArray[$key], 'id' => $wordID );
            }
        }

        // Store every new keyword
        $addRelationWordArray = array();
        foreach ( $newWordArray as $newword )
        {
            $keyword = trim( $newword['keyword'] );
            $keyword = $db->escapeString( $keyword );
            $parent = trim( $newword['parent_id'] );
            $parent = $db->escapeString( $parent );
            $current_time = time();
            $db->query( "INSERT INTO eztags ( keyword, parent_id, modified ) VALUES ( '$keyword', '$parent', $current_time )" );

            $keywordID = $db->lastSerialID( 'eztags', 'id' );
            $addRelationWordArray[] = array( 'keyword' => $keywordID, 'id' => $keywordID );
        }

        $attributeID = $attribute->attribute( 'id' );
        // Find the words which is new for this attribute
        if ( $attributeID !== null )
        {
            $currentWordArray = $db->arrayQuery( "SELECT eztags.id, eztags.keyword FROM eztags, eztags_attribute_link
                                                   WHERE eztags.id=eztags_attribute_link.keyword_id
                                                   AND eztags_attribute_link.objectattribute_id='$attributeID'" );
        }
        else
            $currentWordArray = array();

        foreach ( $existingWordArray as $existingWord )
        {
            $newWord = true;
            foreach ( $currentWordArray as $currentWord )
            {
                if ( $existingWord['keyword']  == $currentWord['keyword'] )
                {
                    $newWord = false;
                }
            }

            if ( $newWord == true )
            {
                $addRelationWordArray[] = $existingWord;
            }
        }

        // Find the current words no longer used
        $removeWordRelationIDArray = array();
        foreach ( $currentWordArray as $currentWord )
        {
            $stillUsed = false;
            foreach ( $this->KeywordArray as $keyword )
            {
                if ( $keyword == $currentWord['keyword'] )
                    $stillUsed = true;
            }
            if ( !$stillUsed )
            {
                $removeWordRelationIDArray[] = $currentWord['id'];
            }
        }

        if ( count( $removeWordRelationIDArray ) > 0 )
        {
            $removeIDString = $db->generateSQLINStatement( $removeWordRelationIDArray, 'keyword_id', false, true, 'int' );
            $db->query( "DELETE FROM eztags_attribute_link WHERE 
                $removeIDString AND  
                eztags_attribute_link.objectattribute_id='$attributeID'" );
        }

        // Only store relation to new keywords
        // Store relations to keyword for this content object
        foreach ( $addRelationWordArray as $keywordArray )
        {
            $db->query( "INSERT INTO eztags_attribute_link ( keyword_id, objectattribute_id ) VALUES ( '" . $keywordArray['id'] ."', '" . $attribute->attribute( 'id' ) . "' )" );
        }

    }

    /*!
     Fetches the keywords for the given attribute.
    */
    function fetch( $attribute )
    {
        if ( $attribute->attribute( 'id' ) === null )
            return;

        $db = eZDB::instance();
        $words = $db->arrayQuery( "SELECT eztags.keyword, eztags.parent_id FROM eztags_attribute_link, eztags
                                    WHERE eztags_attribute_link.keyword_id=eztags.id AND
                                    eztags_attribute_link.objectattribute_id='" . $attribute->attribute( 'id' ) ."' " );

        $this->ObjectAttributeID = $attribute->attribute( 'id' );
        $wordArray = array();
        foreach ( $words as $w ) {
            $wordArray[] = $w['keyword'].",".$w['parent_id'];
        }

        $wordArray = array_unique( $wordArray );
        foreach ( $wordArray as $wordKey )
        {
            $word = explode( ',', $wordKey );
            if ($word[0] != '') {
                $this->KeywordArray[] = $word[0];
                $this->ParentArray[] = $word[1];
            }
        }
    }

    /*!
     Sets the keyword index
    */
    function setKeywordArray( $keywords, $parents = array() )
    {
        $this->KeywordArray = $keywords;
        $this->ParentArray = $parents;
    }

    /*!
     Returns the keyword index
    */
    function keywordArray( )
    {
        return $this->KeywordArray;
    }

    /*!
     Returns the keywords as a string
    */
    function keywordString()
    {
        return implode( ', ', $this->KeywordArray );
    }

    function parentString()
    {
        return implode( ', ', $this->ParentArray );
    }

    /*!
     Returns the objects which have at least one keyword in common

     \return an array of eZContentObjectTreeNode instances, or null if the attribute is not stored yet
    */
    function relatedObjects()
    {
        $return = false;
        if ( $this->ObjectAttributeID )
        {
            $return = array();

            // Fetch words
            $db = eZDB::instance();

            $wordArray = $db->arrayQuery( "SELECT * FROM eztags_attribute_link
                                           WHERE objectattribute_id='" . $this->ObjectAttributeID ."' " );

            $keywordIDArray = array();
            // Fetch the objects which have one of these words
            foreach ( $wordArray as $word )
            {
                $keywordIDArray[] = $word['keyword_id'];
            }

            $keywordCondition = $db->generateSQLINStatement( $keywordIDArray, 'keyword_id', false, true, 'int' );

            if ( count( $keywordIDArray ) > 0 )
            {
                $objectArray = $db->arrayQuery( "SELECT DISTINCT ezcontentobject_attribute.contentobject_id FROM eztags_attribute_link, ezcontentobject_attribute
                                                  WHERE $keywordCondition AND
                                                        ezcontentobject_attribute.id = eztags_attribute_link.objectattribute_id
                                                        AND  objectattribute_id <> '" . $this->ObjectAttributeID ."' " );

                $objectIDArray = array();
                foreach ( $objectArray as $object )
                {
                    $objectIDArray[] = $object['contentobject_id'];
                }

                if ( count( $objectIDArray ) > 0 )
                {
                    $aNodes = eZContentObjectTreeNode::findMainNodeArray( $objectIDArray );

                    foreach ( $aNodes as $node )
                    {
                        $theObject = $node->object();
                        if ( $theObject->canRead() )
                        {
                            $return[] = $node;
                        }
                    }
                }
            }
        }
        return $return;
    }

}

?>
