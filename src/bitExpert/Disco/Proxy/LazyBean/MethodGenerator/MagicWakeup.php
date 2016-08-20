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

namespace bitExpert\Disco\Proxy\LazyBean\MethodGenerator;

use bitExpert\Disco\BeanFactoryRegistry;
use ProxyManager\Generator\MagicMethodGenerator;
use ProxyManager\Proxy\VirtualProxyInterface;
use ReflectionClass;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\PropertyGenerator;

/**
 * `__wakeup` method for lazy loading value holder objects. Will fetch the
 * dependency from the {@link \bitExpert\Disco\BeanFactory} during the
 * unserialization process.
 */
class MagicWakeup extends MagicMethodGenerator
{
    /**
     * Creates a new {@link \bitExpert\Disco\Proxy\MethodGenerator\MagicWakeup}.
     *
     * @param ReflectionClass $originalClass
     * @param PropertyGenerator $valueHolderProperty
     * @param PropertyGenerator $valueHolderBeanIdProperty
     * @throws InvalidArgumentException
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $valueHolderProperty,
        PropertyGenerator $valueHolderBeanIdProperty
    ) {
        parent::__construct($originalClass, '__wakeup');

        $valueHolder = $valueHolderProperty->getName();
        $valueHolderBeanId = $valueHolderBeanIdProperty->getName();

        $this->setBody(
            '$beanFactory = \\' . BeanFactoryRegistry::class . '::getInstance();' . PHP_EOL . PHP_EOL
            . '$this->' . $valueHolder . ' = $beanFactory->get($this->' . $valueHolderBeanId . ');' . PHP_EOL
            . 'if ($this->' . $valueHolder . ' instanceof \\' . VirtualProxyInterface::class . ') {' . PHP_EOL
            . '    $this->' . $valueHolder . ' = $this->' . $valueHolder . '->getWrappedValueHolderValue();' . PHP_EOL
            . '}' . PHP_EOL
        );
    }
}
