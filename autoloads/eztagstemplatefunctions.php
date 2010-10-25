<?php

class eZTagsTemplateFunctions
{
    function eZTagsTemplateFunctions()
    {
    }

    function operatorList()
    {
        return array( 'eztags_parent_string' );
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array( 'eztags_parent_string' => array( 'parent_id' => array( 'type' => 'integer',
                                                'required' => true,
                                                'default' => 0 ) )
        );

    }

    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters )
    {
        switch ( $operatorName )
        {
            case 'eztags_parent_string':
            {
                $operatorValue = $this->generateParentString( $namedParameters['parent_id'] );
            } break;
        }
    }

    function generateParentString($parent_id)
    {
		if($parent_id == 0)
		{
			return '(no parent)';
		}

    	$tag = eZPersistentObject::fetchObject( eZTagsObject::definition(), null, array('id' => $parent_id) );

		$keywordsArray = array();

		while($tag->hasParent())
		{
			$keywordsArray[] = $tag->Keyword;
			$tag = $tag->getParent();
		}

		$keywordsArray[] = $tag->Keyword;

		return implode(' / ', array_reverse($keywordsArray));
    }
}

?>
