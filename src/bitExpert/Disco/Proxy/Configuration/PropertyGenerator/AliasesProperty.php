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

namespace bitExpert\Disco\Proxy\Configuration\PropertyGenerator;

use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;

/**
 * Private property to store alias lookups for the bean instances.
 */
class AliasesProperty extends PropertyGenerator
{
    /**
     * Creates a new {@link \bitExpert\Disco\Proxy\Configuration\PropertyGenerator\AliasesProperty}.
     *
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        parent::__construct(UniqueIdentifierGenerator::getIdentifier('aliases'));

        $this->setVisibility(self::VISIBILITY_PRIVATE);
        $this->setDocBlock('@var array contains a list of aliases and their bean references');
    }
}
