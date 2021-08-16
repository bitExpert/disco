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

use bitExpert\Disco\BeanFactoryConfiguration;
use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\BeanFactoryConfigurationProperty;
use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\BeanPostProcessorsProperty;
use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\ParameterValuesProperty;
use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\SessionBeansProperty;
use ProxyManager\Generator\MethodGenerator;
use Laminas\Code\Generator\ParameterGenerator;

/**
 * `__construct` method for the generated config proxy class.
 */
class Constructor extends MethodGenerator
{
    /**
     * Creates a new {@link \bitExpert\Disco\Proxy\Configuration\MethodGenerator\Constructor}.
     *
     * @param ParameterValuesProperty $parameterValuesProperty
     * @param SessionBeansProperty $sessionBeansProperty
     * @param BeanFactoryConfigurationProperty $beanFactoryConfigurationProperty
     * @param BeanPostProcessorsProperty $beanPostProcessorsProperty
     * @param string[] $beanPostProcessorMethodNames
     */
    public function __construct(
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
        $body .= '// register {@link \\bitExpert\\Disco\\BeanPostProcessor} instances' . PHP_EOL;
        foreach ($beanPostProcessorMethodNames as $methodName) {
            $body .= '$this->' . $beanPostProcessorsProperty->getName() . '[] = $this->' . $methodName . '(); ';
            $body .= PHP_EOL;
        }

        $this->setParameter($beanFactoryConfigurationParameter);
        $this->setParameter($parametersParameter);
        $this->setBody($body);
        $this->setDocBlock('@override constructor');
    }
}
