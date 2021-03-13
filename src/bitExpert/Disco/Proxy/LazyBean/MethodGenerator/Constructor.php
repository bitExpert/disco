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

namespace bitExpert\Disco\Proxy\LazyBean\MethodGenerator;

use ProxyManager\Generator\MethodGenerator;
use ReflectionClass;
use ReflectionProperty;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;

/**
 * `__construct` method for lazy loading value holder objects.
 */
class Constructor extends MethodGenerator
{
    /**
     * Creates a new {@link \bitExpert\Disco\Proxy\LazyBean\MethodGenerator\Constructor}.
     *
     * @param ReflectionClass $originalClass
     * @param PropertyGenerator $initializerProperty
     * @param PropertyGenerator $valueHolderBeanIdProperty
     * @throws InvalidArgumentException
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $initializerProperty,
        PropertyGenerator $valueHolderBeanIdProperty
    ) {
        parent::__construct('__construct');

        $this->setParameter(new ParameterGenerator('beanId'));
        $this->setParameter(new ParameterGenerator('initializer'));

        /* @var $publicProperties \ReflectionProperty[] */
        $publicProperties = $originalClass->getProperties(ReflectionProperty::IS_PUBLIC);
        $unsetProperties = array();

        foreach ($publicProperties as $publicProperty) {
            $unsetProperties[] = '$this->' . $publicProperty->getName();
        }

        $this->setDocBlock(
            '@override constructor for lazy initialization' . PHP_EOL . PHP_EOL
            . '@param \string \$beanId' . PHP_EOL
            . '@param \Closure|null \$initializer'
        );
        $this->setBody(
            ($unsetProperties ? 'unset(' . implode(', ', $unsetProperties) . ');' . PHP_EOL . PHP_EOL : '')
            . '$this->' . $initializerProperty->getName() . ' = $initializer;' . PHP_EOL
            . '$this->' . $valueHolderBeanIdProperty->getName() . ' = $beanId;'
        );
    }
}
