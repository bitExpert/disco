<?php

/*
 * This file is part of the 02003-bitExpertLabs-24-Disco package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace bitExpert\Disco\Proxy\Configuration\MethodGenerator;

use bitExpert\Disco\Proxy\Configuration\BeanPostProcessorsProperty;
use bitExpert\Disco\Proxy\Configuration\ParameterValuesProperty;
use ProxyManager\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use ReflectionClass;

/**
 * `__construct` method for the generated config proxy class.
 */
class Constructor extends MethodGenerator
{
    /**
     * Creates a new {@link \bitExpert\Disco\Proxy\Configuration\MethodGenerator\Constructor}.
     *
     * @param ReflectionClass $originalClass
     * @param BeanPostProcessorsProperty $beanPostProcessorsProperty
     * @param string[] $beanPostProcessorMethodNames
     * @param ParameterValuesProperty $parameterValuesProperty
     */
    public function __construct(
        ReflectionClass $originalClass,
        BeanPostProcessorsProperty $beanPostProcessorsProperty,
        array $beanPostProcessorMethodNames,
        ParameterValuesProperty $parameterValuesProperty
    ) {
        parent::__construct('__construct');

        $parametersParameter = new ParameterGenerator('params');
        $parametersParameter->setDefaultValue([]);

        $body = '$this->' . $parameterValuesProperty->getName() . ' = $' . $parametersParameter->getName() . ";\n";
        $body .= '// register {@link \\bitExpert\\Disco\\BeanPostProcessor} instances' . "\n";
        $body .= '$this->' . $beanPostProcessorsProperty->getName() .
            '[] = new \bitExpert\Disco\BeanFactoryPostProcessor();' . "\n";
        foreach ($beanPostProcessorMethodNames as $methodName) {
            $body .= '$this->' . $beanPostProcessorsProperty->getName() . '[] = $this->' . $methodName . '(); ' . "\n";
        }

        $this->setParameter($parametersParameter);
        $this->setBody($body);
        $this->setDocBlock("@override constructor");
    }
}
