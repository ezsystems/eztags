<?php

/**
 * eZTagsTemplateFunctions class implements eztags tpl operator methods
 * 
 */
class eZTagsTemplateFunctions
{
    /**
     * Return an array with the template operator name.
     * 
     * @return array
     */
    function operatorList()
    {
        return array( 'eztags_parent_string', 'latest_tags' );
    }

    /**
     * Return true to tell the template engine that the parameter list exists per operator type,
     * this is needed for operator classes that have multiple operators.
     * 
     * @return bool
     */
    function namedParameterPerOperator()
    {
        return true;
    }

    /**
     * Returns an array of named parameters, this allows for easier retrieval
     * of operator parameters. This also requires the function modify() has an extra
     * parameter called $namedParameters.
     * 
     * @return array
     */
    function namedParameterList()
    {
        return array( 'eztags_parent_string' => array( 'parent_id' => array( 'type' => 'integer',
                                                'required' => true,
                                                'default' => 0 ) ),
						'latest_tags' => array( 'limit' => array( 'type' => 'integer',
                                                'required' => false,
                                                'default' => 10 ) )
        );

    }

    /**
     * Executes the PHP function for the operator cleanup and modifies $operatorValue.
     * 
     * @param eZTemplate $tpl
     * @param string $operatorName
     * @param array $operatorParameters
     * @param string $rootNamespace
     * @param string $currentNamespace
     * @param mixed $operatorValue
     * @param array $namedParameters
     */
    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters )
    {
        switch ( $operatorName )
        {
            case 'eztags_parent_string':
            {
                $operatorValue = $this->generateParentString( $namedParameters['parent_id'] );
            } break;
            case 'latest_tags':
            {
                $operatorValue = $this->fetchLatestTags( $namedParameters['limit'] );
            } break;
        }
    }

    /**
     * Generates tag heirarchy string for given parent ID
     * 
     * @param integer $parent_id
     * @return string
     */
    function generateParentString($parent_id)
    {
        if($parent_id == 0)
        {
            return '(' . ezpI18n::tr( 'extension/eztags/tags/edit', 'no parent' ) . ')';
        }

        $tag = eZTagsObject::fetch($parent_id);

        $keywordsArray = array();

        while($tag->hasParent())
        {
            $keywordsArray[] = $tag->Keyword;
            $tag = $tag->getParent();
        }

        $keywordsArray[] = $tag->Keyword;

        return implode(' / ', array_reverse($keywordsArray));
    }

    /**
     * Returns $limit latest tags
     * 
     * @param integer $limit
     * @return array
     */
    function fetchLatestTags($limit)
    {
    	return eZPersistentObject::fetchObjectList( eZTagsObject::definition(), null, array('main_tag_id' => 0), array('modified' => 'desc'), $limit );
    }
}

?>
