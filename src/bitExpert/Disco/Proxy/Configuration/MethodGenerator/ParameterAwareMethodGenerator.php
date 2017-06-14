<?php

/*
 * This file is part of the Disco package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types = 1);

namespace bitExpert\Disco\Proxy\Configuration\MethodGenerator;

use bitExpert\Disco\Annotations\Parameter;
use Zend\Code\Generator\MethodGenerator;

/**
 * Base class for all annotations that are parameter-aware.
 */
class ParameterAwareMethodGenerator extends MethodGenerator
{
    /**
     * Converts the Parameter annotations to the respective getParameter() method calls to retrieve the configuration
     * values.
     *
     * @param Parameter[] $methodParameters
     * @param GetParameter $parameterValuesMethod
     * @return string
     */
    protected static function convertMethodParamsToString(
        array $methodParameters,
        GetParameter $parameterValuesMethod
    ) : string {
        $parameters = [];
        foreach ($methodParameters as $methodParameter) {
            /** @var $methodParameter Parameter */
            $name = $methodParameter->getName();
            $defaultValue = $methodParameter->getDefaultValue();
            $required = $methodParameter->isRequired() ? 'true' : 'false';
            if (is_string($defaultValue)) {
                $defaultValue = '"' . $defaultValue . '"';
            } elseif (is_null($defaultValue)) {
                $defaultValue = 'null';
            } elseif (is_bool($defaultValue)) {
                $defaultValue = ($defaultValue) ? 'true' : 'false';
            }

            if (!empty($defaultValue)) {
                $parameters[] = '$this->' . $parameterValuesMethod->getName() . '("' . $name . '", ' . $required .
                    ', ' . $defaultValue . ')';
            } else {
                $parameters[] = '$this->' . $parameterValuesMethod->getName() . '("' . $name . '", ' . $required .
                    ')';
            }
        }

        return implode(', ', $parameters);
    }
}
