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

use bitExpert\Disco\BeanFactoryConfiguration;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\VoidCache;
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
     * @param BeanFactoryConfiguration $config
     */
    public function __construct(BeanFactoryConfiguration $config = null)
    {
        $proxyManagerConfiguration = null;
        $annotationCache = new VoidCache();
        if ($config !== null) {
            $proxyManagerConfiguration = new Configuration();
            $proxyManagerConfiguration->setProxiesTargetDir($config->getProxyTargetDir());
            if ($config->getProxyGeneratorStrategy() !== null) {
                $proxyManagerConfiguration->setGeneratorStrategy($config->getProxyGeneratorStrategy());
            }
            $annotationCache = $config->getAnnotationCache();
        }
        parent::__construct($proxyManagerConfiguration);

        $this->generator = new ConfigurationGenerator($annotationCache);
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
