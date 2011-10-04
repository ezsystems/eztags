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
        return array( 'eztags_parent_string', 'latest_tags', 'user_limitations' );
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
        return array( 'eztags_parent_string' => array( 'tag_id' => array( 'type'     => 'integer',
                                                                          'required' => true,
                                                                          'default'  => 0 ) ),
                      'latest_tags'          => array( 'limit'  => array( 'type'     => 'integer',
                                                                          'required' => false,
                                                                          'default'  => 10 ) ),
                      'user_limitations'     => array( 'module' => array( 'type'     => 'string',
                                                                          'required' => true,
                                                                          'default'  => '' ),
                                                                          'function' => array( 'type'     => 'string',
                                                                                               'required' => true,
                                                                                               'default'  => '' ) ) );
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
                $operatorValue = self::generateParentString( $namedParameters['tag_id'] );
            } break;
            case 'latest_tags':
            {
                $operatorValue = self::fetchLatestTags( $namedParameters['limit'] );
            } break;
            case 'user_limitations':
            {
                $operatorValue = self::getSimplifiedUserAccess( $namedParameters['module'], $namedParameters['function'] );
            } break;
        }
    }

    /**
     * Generates tag heirarchy string for given tag ID
     *
     * @static
     * @param integer $tagID
     * @return string
     */
    static function generateParentString( $tagID )
    {
        $tag = eZTagsObject::fetch( $tagID );
        if ( !$tag instanceof eZTagsObject )
            return '(' . ezpI18n::tr( 'extension/eztags/tags/edit', 'no parent' ) . ')';

        $synonymsCount = $tag->getSynonymsCount();

        $keywordsArray = array();

        while ( $tag->hasParent() )
        {
            $keywordsArray[] = ( $synonymsCount > 0 ) ? $tag->attribute( 'keyword' ) . ' (+' . $synonymsCount . ')' : $tag->attribute( 'keyword' );
            $tag = $tag->getParent();
            $synonymsCount = $tag->getSynonymsCount();
        }

        $keywordsArray[] = ( $synonymsCount > 0 ) ? $tag->attribute( 'keyword' ) . ' (+' . $synonymsCount . ')' : $tag->attribute( 'keyword' );

        return implode( ' / ', array_reverse( $keywordsArray ) );
    }

    /**
     * Returns $limit latest tags
     *
     * @static
     * @param integer $limit
     * @return array
     */
    static function fetchLatestTags( $limit )
    {
        return eZPersistentObject::fetchObjectList( eZTagsObject::definition(), null,
                                                    array( 'main_tag_id' => 0 ),
                                                    array( 'modified' => 'desc' ),
                                                    array( 'limit' => $limit ) );
    }

    /**
     * Shorthand method to check user access policy limitations for a given module/policy function.
     * Returns the same array as eZUser::hasAccessTo(), with "simplifiedLimitations".
     * 'simplifiedLimitations' array holds all the limitations names as defined in module.php.
     * If your limitation name is not defined as a key, then your user has full access to this limitation
     *
     * @static
     * @param string $module Name of the module
     * @param string $function Name of the policy function ($FunctionList element in module.php)
     * @return array
     */

    static function getSimplifiedUserAccess( $module, $function )
    {
        $user = eZUser::currentUser();
        $userAccess = $user->hasAccessTo( $module, $function );

        $userAccess['simplifiedLimitations'] = array();
        if ( $userAccess['accessWord'] == 'limited' )
        {
            foreach ( $userAccess['policies'] as $policy )
            {
                foreach ( $policy as $limitationName => $limitationList )
                {
                    foreach ( $limitationList as $limitationValue )
                    {
                        $userAccess['simplifiedLimitations'][$limitationName][] = $limitationValue;
                    }

                    $userAccess['simplifiedLimitations'][$limitationName] = array_unique( $userAccess['simplifiedLimitations'][$limitationName] );
                }
            }
        }
        return $userAccess;
    }
}

?>
