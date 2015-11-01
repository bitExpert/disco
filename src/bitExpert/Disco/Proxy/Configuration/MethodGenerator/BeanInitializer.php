<?php

/*
 * This file is part of the Disco package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bitExpert\Disco\Proxy\Configuration\MethodGenerator;

use bitExpert\Disco\FactoryBean;
use bitExpert\Disco\InitializedBean;
use bitExpert\Disco\Proxy\Configuration\BeanPostProcessorsProperty;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\ParameterGenerator;
use ReflectionClass;

/**
 * `initializeBean` method for the generated config proxy class.
 */
class BeanInitializer extends MethodGenerator
{
    /**
     * Creates a new {@link \bitExpert\Disco\Proxy\Configuration\MethodGenerator\BeanInitializer}.
     *
     * @param ReflectionClass $originalClass
     * @param BeanPostProcessorsProperty $postProcessorsProperty
     */
    public function __construct(ReflectionClass $originalClass, BeanPostProcessorsProperty $postProcessorsProperty)
    {
        parent::__construct('initializeBean');

        $beanParameter = new ParameterGenerator('bean');
        $beanParameter->setPassedByReference(true);
        $beanNameParameter = new ParameterGenerator('beanName');

        $body = 'if ($bean instanceof \\' . FactoryBean::class . ') {' . "\n";
        $body .= '    $bean = $bean->getObject();' . "\n";
        $body .= '}' . "\n\n";

        $body .= 'if ($bean instanceof \\' . InitializedBean::class . ') {' . "\n";
        $body .= '    $bean->postInitialization();' . "\n";
        $body .= '}' . "\n\n";

        $body .= 'foreach ($this->' . $postProcessorsProperty->getName() . ' as $postProcessor) {' . "\n";
        $body .= '    $postProcessor->postProcess($bean, $beanName);' . "\n";
        $body .= '}' . "\n";

        $this->setParameter($beanParameter);
        $this->setParameter($beanNameParameter);
        $this->setVisibility(self::VISIBILITY_PROTECTED);
        $this->setBody($body);
    }
}
