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

use bitExpert\Disco\BeanFactoryConfiguration;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;

/**
 * Private property to store the {@link \bitExpert\Disco\BeanFactoryConfiguration}.
 */
class BeanFactoryConfigurationProperty extends PropertyGenerator
{
    /**
     * Creates a new {@link \bitExpert\Disco\Proxy\Configuration\PropertyGenerator\BeanFactoryConfigurationProperty}.
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
