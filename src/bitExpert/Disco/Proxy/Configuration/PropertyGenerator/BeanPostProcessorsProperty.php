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
 * The property collects all registered instances of {@link \bitExpert\Disco\BeanPostProcessor}s.
 */
class BeanPostProcessorsProperty extends PropertyGenerator
{
    /**
     * Creates a new {@link \bitExpert\Disco\Proxy\Configuration\PropertyGenerator\BeanPostProcessorsProperty}.
     *
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        parent::__construct(UniqueIdentifierGenerator::getIdentifier('postProcessors'));

        $this->setDefaultValue([]);
        $this->setVisibility(self::VISIBILITY_PRIVATE);
        $this->setDocBlock('@var ' . \bitExpert\Disco\BeanPostProcessor::class . '[]');
    }
}
