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
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;

class BeanPostProcessorMethod extends ParameterAwareMethodGenerator
{
    /**
     * Creates a new {@link \bitExpert\Disco\Proxy\Configuration\MethodGenerator\BeanPostProcessorMethod}.
     *
     * @param MethodReflection $originalMethod
     * @param Parameter[] $beanPostProcessorParameters
     * @param GetParameter $parameterValuesMethod
     * @return MethodGenerator
     * @throws InvalidArgumentException
     */
    public static function generateMethod(
        MethodReflection $originalMethod,
        array $beanPostProcessorParameters,
        GetParameter $parameterValuesMethod
    ): MethodGenerator {
        $method = static::fromReflection($originalMethod);

        $methodParams = static::convertMethodParamsToString(
            $beanPostProcessorParameters,
            $parameterValuesMethod
        );
        $beanId = $originalMethod->name;
        $body = 'return parent::' . $beanId . '(' . $methodParams . ');' . PHP_EOL;

        $method->setBody($body);
        $method->setDocBlock('{@inheritDoc}');
        return $method;
    }
}
