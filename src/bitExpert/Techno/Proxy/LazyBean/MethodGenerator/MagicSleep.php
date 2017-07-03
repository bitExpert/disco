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

namespace bitExpert\Techno\Proxy\LazyBean\MethodGenerator;

use ProxyManager\Generator\MagicMethodGenerator;
use ReflectionClass;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\PropertyGenerator;

/**
 * `__sleep` method for lazy loading value holder objects. Will return the
 * beanId for the serialization process to that the "real" instance can be
 * fetched again when the `__wakeup` method gets called. This way we ensure
 * that singleton dependencies are properly fetched again from the
 * {@link \bitExpert\Techno\BeanFactory}.
 */
class MagicSleep extends MagicMethodGenerator
{
    /**
     * Creates a new {@link \bitExpert\Techno\Proxy\MethodGenerator\MagicSleep}.
     *
     * @param ReflectionClass $originalClass
     * @param PropertyGenerator $initializerProperty
     * @param PropertyGenerator $valueHolderProperty
     * @param PropertyGenerator $valueHolderBeanIdProperty
     * @throws InvalidArgumentException
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $initializerProperty,
        PropertyGenerator $valueHolderProperty,
        PropertyGenerator $valueHolderBeanIdProperty
    ) {
        parent::__construct($originalClass, '__sleep');

        $initializer = $initializerProperty->getName();
        $valueHolder = $valueHolderProperty->getName();
        $valueHolderBeanId = $valueHolderBeanIdProperty->getName();

        $this->setBody(
            '$this->' . $initializer . ' && $this->' . $initializer
            . '->__invoke($this->' . $valueHolder . ', $this, \'__sleep\', array(), $this->'
            . $initializer . ');' . PHP_EOL . PHP_EOL
            . 'return array(' . var_export($valueHolderBeanId, true) . ');'
        );
    }
}
