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

namespace bitExpert\Techno\Proxy\Configuration\PropertyGenerator;

use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\PropertyGenerator;

/**
 * The property collects all registered instances of {@link \bitExpert\Techno\BeanPostProcessor}s.
 */
class BeanPostProcessorsProperty extends PropertyGenerator
{
    /**
     * Creates a new {@link \bitExpert\Techno\Proxy\Configuration\PropertyGenerator\BeanPostProcessorsProperty}.
     *
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        parent::__construct(UniqueIdentifierGenerator::getIdentifier('postProcessors'));

        $this->setDefaultValue([]);
        $this->setVisibility(self::VISIBILITY_PRIVATE);
        $this->setDocBlock('@var ' . \bitExpert\Techno\BeanFactoryPostProcessor::class . '[]');
    }
}
