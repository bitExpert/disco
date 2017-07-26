<?php

/*
 * This file is part of the Disco package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

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
    ): string {
        $parameters = [];
        foreach ($methodParameters as $methodParameter) {
            /** @var $methodParameter Parameter */
            $defaultValue = $methodParameter->getDefaultValue();
            switch (\gettype($defaultValue)) {
                case 'string':
                    $defaultValue = '"' . $defaultValue . '"';
                    break;
                case 'boolean':
                    $defaultValue = $defaultValue ? 'true' : 'false';
                    break;
                case 'NULL':
                    $defaultValue = 'null';
                    break;
                default:
                    break;
            }

            $template = empty($defaultValue) ? '$this->%s("%s", %s)' : '$this->%s("%s", %s, %s)';
            $required = $methodParameter->isRequired() ? 'true' : 'false';
            $methodName = $parameterValuesMethod->getName();
            $parameters[] = \sprintf($template, $methodName, $methodParameter->getName(), $required, $defaultValue);
        }

        return \implode(', ', $parameters);
    }
}
