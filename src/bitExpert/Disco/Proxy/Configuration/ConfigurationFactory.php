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

namespace bitExpert\Disco\Proxy\Configuration;

use bitExpert\Disco\BeanFactoryConfiguration;
use ProxyManager\Factory\AbstractBaseFactory;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;

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
     * @param BeanFactoryConfiguration $config
     */
    public function __construct(BeanFactoryConfiguration $config)
    {
        parent::__construct($config->getProxyManagerConfiguration());

        $this->generator = new ConfigurationGenerator();
    }

    /**
     * Creates an instance of the given $configClassName.
     *
     * @param BeanFactoryConfiguration $config
     * @param string $configClassName name of the configuration class
     * @param array $parameters
     * @return object
     */
    public function createInstance(
        BeanFactoryConfiguration $config,
        string $configClassName,
        array $parameters = []
    ): object {
        $proxyClassName = $this->generateProxy($configClassName);
        return new $proxyClassName($config, $parameters);
    }

    /**
     * {@inheritDoc}
     */
    protected function getGenerator(): ProxyGeneratorInterface
    {
        return $this->generator;
    }
}
