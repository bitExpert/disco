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

use bitExpert\Techno\BeanFactoryConfiguration;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Private property to store the {@link \bitExpert\Techno\BeanFactoryConfiguration}.
 */
class BeanFactoryConfigurationProperty extends PropertyGenerator
{
    /**
     * Creates a new {@link \bitExpert\Techno\Proxy\Configuration\PropertyGenerator\BeanFactoryConfigurationProperty}.
     *
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        parent::__construct(UniqueIdentifierGenerator::getIdentifier('config'));

        $this->setVisibility(self::VISIBILITY_PRIVATE);
        $this->setDocBlock('@var ' . BeanFactoryConfiguration::class);
    }
}
