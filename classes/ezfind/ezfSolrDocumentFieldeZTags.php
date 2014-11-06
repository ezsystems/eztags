<?php
/*
 * Indexing logic for the datatype eztags - ezfind uses it
 * 
 */

class ezfSolrDocumentFieldeZTags extends ezfSolrDocumentFieldBase
{
    /**
     * (non-PHPdoc)
     * @see ezfSolrDocumentFieldBase::getData()
     */    
    public function getData()
    {
        $eztags = new eZTags();
        $eztags->createFromAttribute( $this->ContentObjectAttribute );

        $tags = $this->getSearchTags( $eztags );
        
        $data = array();
        if( !empty( $tags ) )
        {
            foreach( $tags as $tag )
            {
                $data[ $tag->attribute( 'id' ) ] = $tag->attribute( 'keyword' );
            }
        }
        
        return array( 
            'attr_eztags_lk'        => implode( ',', $data ),
            'submeta_eztagids____ms' => array_keys( $data ),
        );
    }
    
    private function getSearchTags( $eztags )
    {
        $return = $eztags->attribute( 'tags' );

        $ini = eZINI::instance( 'eztags.ini' );
        
        if( $ini->variable( 'SearchSettings', 'IndexSynonyms' ) === 'disabled' )
        {
            $tagsFiltered = array();
            foreach( $return as $tag )
            {
                if( $tag->isSynonym() )
                {
                    $tagsFiltered[ $tag->attribute( 'id' ) ] = $tag->attribute( 'main_tag' );
                }
                else
                {
                    //PEK: main_tag is itself? Would make things easier.
                    $tagsFiltered[ $tag->attribute( 'id' ) ] = $tag;
                }
            }
            
            $return = $tagsFiltered;
        }

        return $return;
    }
}
