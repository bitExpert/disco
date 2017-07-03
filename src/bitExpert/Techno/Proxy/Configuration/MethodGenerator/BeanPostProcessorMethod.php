<?php

/*
 * This file is part of the Techno package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace bitExpert\Techno\Proxy\Configuration\MethodGenerator;

use bitExpert\Techno\Annotations\BeanPostProcessor;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Reflection\MethodReflection;

class BeanPostProcessorMethod extends ParameterAwareMethodGenerator
{
    /**
     * Creates a new {@link \bitExpert\Techno\Proxy\Configuration\MethodGenerator\BeanPostProcessorMethod}.
     *
     * @param MethodReflection $originalMethod
     * @param BeanPostProcessor $beanPostProcessorMetadata
     * @param GetParameter $parameterValuesMethod
     * @return MethodGenerator
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     */
    public static function generateMethod(
        MethodReflection $originalMethod,
        BeanPostProcessor $beanPostProcessorMetadata,
        GetParameter $parameterValuesMethod
    ) {
        $method = static::fromReflection($originalMethod);

        $methodParams = static::convertMethodParamsToString(
            $beanPostProcessorMetadata->getParameters(),
            $parameterValuesMethod
        );
        $beanId = $originalMethod->name;
        $body = 'return parent::' . $beanId . '(' . $methodParams . ');' . PHP_EOL;

        $method->setBody($body);
        $method->setDocBlock('{@inheritDoc}');
        return $method;
    }
}
