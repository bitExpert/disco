<?php

/*
 * This file is part of the 02003-bitExpertLabs-24-Techno package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace bitExpert\Techno\Proxy\Configuration\MethodGenerator;

use bitExpert\Techno\BeanFactoryConfiguration;
use bitExpert\Techno\Proxy\Configuration\PropertyGenerator\BeanFactoryConfigurationProperty;
use bitExpert\Techno\Proxy\Configuration\PropertyGenerator\BeanPostProcessorsProperty;
use bitExpert\Techno\Proxy\Configuration\PropertyGenerator\ParameterValuesProperty;
use bitExpert\Techno\Proxy\Configuration\PropertyGenerator\SessionBeansProperty;
use ProxyManager\Generator\MethodGenerator;
use ReflectionClass;
use Zend\Code\Generator\ParameterGenerator;

/**
 * `__construct` method for the generated config proxy class.
 */
class Constructor extends MethodGenerator
{
    /**
     * Creates a new {@link \bitExpert\Techno\Proxy\Configuration\MethodGenerator\Constructor}.
     *
     * @param ReflectionClass $originalClass
     * @param ParameterValuesProperty $parameterValuesProperty
     * @param SessionBeansProperty $sessionBeansProperty
     * @param BeanFactoryConfigurationProperty $beanFactoryConfigurationProperty
     * @param BeanPostProcessorsProperty $beanPostProcessorsProperty
     * @param string[] $beanPostProcessorMethodNames
     */
    public function __construct(
        ReflectionClass $originalClass,
        ParameterValuesProperty $parameterValuesProperty,
        SessionBeansProperty $sessionBeansProperty,
        BeanFactoryConfigurationProperty $beanFactoryConfigurationProperty,
        BeanPostProcessorsProperty $beanPostProcessorsProperty,
        array $beanPostProcessorMethodNames
    ) {
        parent::__construct('__construct');

        $beanFactoryConfigurationParameter = new ParameterGenerator('config');
        $beanFactoryConfigurationParameter->setType(BeanFactoryConfiguration::class);

        $parametersParameter = new ParameterGenerator('params');
        $parametersParameter->setDefaultValue([]);

        $body = '$this->' . $parameterValuesProperty->getName() . ' = $' . $parametersParameter->getName() .
            ';' . PHP_EOL;
        $body .= '$this->' . $beanFactoryConfigurationProperty->getName() .
            ' = $' . $beanFactoryConfigurationParameter->getName() . ';' . PHP_EOL;
        $body .= '$this->' . $sessionBeansProperty->getName() . ' = $' . $beanFactoryConfigurationParameter->getName() .
            '->getSessionBeanStore();' . PHP_EOL;
        $body .= '// register {@link \\bitExpert\\Techno\\BeanPostProcessor} instances' . PHP_EOL;
        foreach ($beanPostProcessorMethodNames as $methodName) {
            $body .= '$this->' . $beanPostProcessorsProperty->getName() . '[] = $this->' . $methodName . '(); ';
            $body .= PHP_EOL;
        }

        $this->setParameter($beanFactoryConfigurationParameter);
        $this->setParameter($parametersParameter);
        $this->setBody($body);
        $this->setDocBlock("@override constructor");
    }
}
