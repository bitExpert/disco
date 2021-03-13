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
 * The forceLazyInit property is used as a marker to make sure that lazy dependencies are created
 * for session-aware beans. Dependencies of session beans need to be lazy so that they are rebuilt
 * for every new request (unless the dependency is marked is session-aware itself).
 */
class ForceLazyInitProperty extends PropertyGenerator
{
    /**
     * Creates a new {@link \bitExpert\Disco\Proxy\Configuration\PropertyGenerator\ForceLazyInitProperty}.
     *
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        parent::__construct(UniqueIdentifierGenerator::getIdentifier('forceLazyInit'));

        $this->setVisibility(self::VISIBILITY_PRIVATE);
        $this->setDocBlock('@var bool flag to toggle if a bean gets wrapped by a LazyProxy or not');
    }
}
