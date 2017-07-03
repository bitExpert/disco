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

use bitExpert\Techno\Proxy\Configuration\PropertyGenerator\AliasesProperty;
use ProxyManager\Generator\MethodGenerator;
use ReflectionClass;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\ParameterGenerator;

/**
 * `hasAlias` method for the generated config proxy class.
 */
class HasAlias extends MethodGenerator
{
    /**
     * Creates a new {@link \bitExpert\Techno\Proxy\Configuration\MethodGenerator\HasAlias}.
     *
     * @param ReflectionClass $originalClass
     * @param AliasesProperty $aliasesProperty
     * @throws InvalidArgumentException
     */
    public function __construct(ReflectionClass $originalClass, AliasesProperty $aliasesProperty)
    {
        parent::__construct('hasAlias');

        $aliasParameter = new ParameterGenerator('alias');
        $aliasParameter->setType('string');

        $this->setParameter($aliasParameter);
        $this->setVisibility(self::VISIBILITY_PUBLIC);
        $this->setReturnType('bool');
        $this->setBody(
            'return !empty($' . $aliasParameter->getName() . ') && ' .
            'isset($this->' . $aliasesProperty->getName() . '[$' . $aliasParameter->getName() . ']);'
        );
    }
}
