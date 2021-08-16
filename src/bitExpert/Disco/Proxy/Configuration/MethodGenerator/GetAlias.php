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

namespace bitExpert\Disco\Proxy\Configuration\MethodGenerator;

use bitExpert\Disco\BeanNotFoundException;
use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\AliasesProperty;
use ProxyManager\Generator\MethodGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\ParameterGenerator;

/**
 * `getAlias` method for the generated config proxy class.
 */
class GetAlias extends MethodGenerator
{
    /**
     * Creates a new {@link \bitExpert\Disco\Proxy\Configuration\MethodGenerator\GetAlias}.
     *
     * @param AliasesProperty $aliasesProperty
     * @throws InvalidArgumentException
     */
    public function __construct(AliasesProperty $aliasesProperty)
    {
        parent::__construct('getAlias');

        $aliasParameter = new ParameterGenerator('alias');
        $aliasParameter->setType('string');

        $body = 'if ($this->hasAlias($' . $aliasParameter->getName() . ')) {' . PHP_EOL;
        $body .= '    $methodname = $this->' . $aliasesProperty->getName() . '[$' . $aliasParameter->getName() . '];' .
            PHP_EOL;
        $body .= '    return $this->$methodname();' . PHP_EOL;
        $body .= '}' . PHP_EOL . PHP_EOL;
        $body .= 'throw new ' . BeanNotFoundException::class . '(sprintf(\'Alias "%s" is not defined!\', $' .
            $aliasParameter->getName() . '));' . PHP_EOL;

        $this->setParameter($aliasParameter);
        $this->setVisibility(self::VISIBILITY_PUBLIC);
        $this->setBody($body);
    }
}
