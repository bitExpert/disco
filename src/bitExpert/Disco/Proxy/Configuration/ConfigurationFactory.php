<?php

/*
 * This file is part of the Disco package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bitExpert\Disco\Proxy\Configuration;

use Doctrine\Common\Cache\Cache;
use ProxyManager\Configuration;
use ProxyManager\Factory\AbstractBaseFactory;

/**
 * Factory responsible of producing a proxy of the configuration instance.
 */
class ConfigurationFactory extends AbstractBaseFactory
{
    /**
     * @var ConfigurationGenerator
     */
    private $generator;

    /**
     * Creates a new {@link \bitExpert\Disco\Proxy\Configuration\ConfigurationFactory}.
     *
     * @param Cache $cache
     * @param Configuration $configuration
     */
    public function __construct(Cache $cache = null, Configuration $configuration = null)
    {
        parent::__construct($configuration);

        $this->generator = new ConfigurationGenerator($cache);
    }

    /**
     * Creates an instance of the given $configClassName.
     *
     * @param string $configClassName name of the configuration class
     * @param array $parameters
     * @return object
     */
    public function createInstance($configClassName, array $parameters = [])
    {
        $proxyClassName = $this->generateProxy($configClassName);

        return new $proxyClassName($parameters);
    }

    /**
     * {@inheritDoc}
     */
    protected function getGenerator()
    {
        return $this->generator;
    }
}
