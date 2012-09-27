<?php

/**
 * eZTagsTemplateFunctions class implements eztags template operator methods
 */
class eZTagsTemplateFunctions
{
    /**
     * Return an array with the list of template operator names
     *
     * @return array
     */
    public function operatorList()
    {
        return array( 'eztags_parent_string', 'latest_tags', 'user_limitations', 'tag_icon' );
    }

    /**
     * Return true to tell the template engine that the parameter list exists per operator type,
     * this is needed for operator classes that have multiple operators.
     *
     * @return bool
     */
    public function namedParameterPerOperator()
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
    public function namedParameterList()
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
                                                                                               'default'  => '' ) ),
                      'tag_icon'             => array( 'first'  => array( 'type'     => 'string',
                                                                          'required' => false,
                                                                          'default'  => '' ),
                                                       'second' => array( 'type'     => 'string',
                                                                          'required' => false,
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
    public function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters )
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
            case 'tag_icon':
            {
                if ( $operatorValue === null )
                    $operatorValue = self::getTagIcon( $namedParameters['first'], $namedParameters['second'] );
                else
                {
                    $operatorValue = self::getTagIcon(
                        $operatorValue,
                        empty( $namedParameters['first'] ) ? 'small' : $namedParameters['first']
                    );
                }
            } break;
        }
    }

    /**
     * Generates tag hierarchy string for given tag ID
     *
     * @static
     *
     * @param int $tagID
     *
     * @return string
     */
    static public function generateParentString( $tagID )
    {
        $tag = eZTagsObject::fetchWithMainTranslation( $tagID );
        if ( !$tag instanceof eZTagsObject )
            return '(' . ezpI18n::tr( 'extension/eztags/tags/edit', 'no parent' ) . ')';

        return $tag->getParentString();
    }

    /**
     * Returns $limit latest tags
     * Deprecated: use fetch( tags, latest_tags, hash( ... ) )
     *
     * @deprecated
     *
     * @static
     *
     * @param int $limit
     *
     * @return eZTagsObject[]
     */
    static public function fetchLatestTags( $limit )
    {
        return eZTagsFunctionCollection::fetchLatestTags( 0, $limit );
    }

    /**
     * Shorthand method to check user access policy limitations for a given module/policy function.
     * Returns the same array as eZUser::hasAccessTo(), with "simplifiedLimitations".
     * 'simplifiedLimitations' array holds all the limitations names as defined in module.php.
     * If your limitation name is not defined as a key, then your user has full access to this limitation
     *
     * @static
     *
     * @param string $module Name of the module
     * @param string $function Name of the policy function ( $FunctionList element in module.php )
     *
     * @return array
     */
    static public function getSimplifiedUserAccess( $module, $function )
    {
        $user = eZUser::currentUser();
        $userAccess = $user->hasAccessTo( $module, $function );

        $userAccess['simplifiedLimitations'] = array();
        if ( $userAccess['accessWord'] != 'limited' )
            return $userAccess;

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

        return $userAccess;
    }

    /**
     * Returns the full URL of the tag icon image
     *
     * @static
     *
     * @param string $icon
     * @param string $size
     *
     * @return string
     */
    static public function getTagIcon( $icon, $size = 'small' )
    {
        return eZURLOperator::eZImage( null, 'tag_icons/' . $size . '/' . $icon, 'ezimage' );
    }
}
