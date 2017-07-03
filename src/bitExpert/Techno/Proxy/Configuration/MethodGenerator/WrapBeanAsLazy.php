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

use bitExpert\Techno\Proxy\Configuration\PropertyGenerator\BeanFactoryConfigurationProperty;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ReflectionClass;
use Zend\Code\Generator\ParameterGenerator;

/**
 * `wrapBeanAsLazy` method for lazy loading value holder objects.
 */
class WrapBeanAsLazy extends MethodGenerator
{
    /**
     * Creates a new {@link \bitExpert\Techno\Proxy\LazyBean\MethodGenerator\Constructor}.
     *
     * @param ReflectionClass $originalClass
     * @param BeanFactoryConfigurationProperty $beanFactoryConfigurationProperty
     */
    public function __construct(
        ReflectionClass $originalClass,
        BeanFactoryConfigurationProperty $beanFactoryConfigurationProperty
    ) {
        parent::__construct(UniqueIdentifierGenerator::getIdentifier('wrapBeanAsLazy'));

        $this->setParameter(new ParameterGenerator('beanId'));
        $this->setParameter(new ParameterGenerator('beanType'));
        $this->setParameter(new ParameterGenerator('instance'));

        $content = '$factory = new \\' . \bitExpert\Techno\Proxy\LazyBean\LazyBeanFactory::class . '($beanId, $this->' .
            $beanFactoryConfigurationProperty->getName() . '->getProxyManagerConfiguration());' . PHP_EOL;
        $content .= '$initializer = function (&$wrappedObject, \\' . \ProxyManager\Proxy\LazyLoadingInterface::class .
            ' $proxy, $method, array $parameters, &$initializer) use($instance) {' . PHP_EOL;
        $content .= '    $initializer = null;' . PHP_EOL;
        $content .= '    $wrappedObject = $instance;' . PHP_EOL;
        $content .= '    return true;' . PHP_EOL;
        $content .= '};' . PHP_EOL;
        $content .= PHP_EOL;

        $content .= '$initializer->bindTo($this);' . PHP_EOL;
        $content .= 'return $factory->createProxy($beanType, $initializer);' . PHP_EOL;

        $this->setVisibility(self::VISIBILITY_PROTECTED);
        $this->setBody($content);
    }
}
