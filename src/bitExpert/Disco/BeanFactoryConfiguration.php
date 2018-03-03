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

namespace bitExpert\Disco;

use bitExpert\Disco\Store\BeanStore;
use bitExpert\Disco\Store\SerializableBeanStore;
use InvalidArgumentException;
use ProxyManager\Autoloader\AutoloaderInterface;
use ProxyManager\Configuration;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use ProxyManager\GeneratorStrategy\GeneratorStrategyInterface;

/**
 * BeanFactory configuration class.
 *
 * @api
 */
class BeanFactoryConfiguration
{
    /**
     * @var BeanStore
     */
    protected $sessionBeanStore;
    /**
     * @var string
     */
    protected $proxyTargetDir;
    /**
     * @var GeneratorStrategyInterface
     */
    protected $proxyWriterGenerator;
    /**
     * @var AutoloaderInterface
     */
    protected $proxyAutoloader;

    /**
     * Creates a new {@link \bitExpert\Disco\BeanFactoryConfiguration}.
     *
     * @param string $proxyTargetDir
     * @throws InvalidArgumentException
     */
    public function __construct($proxyTargetDir)
    {
        try {
            $proxyFileLocator = new FileLocator($proxyTargetDir);
        } catch (\Exception $e) {
            throw new InvalidArgumentException(
                sprintf(
                    'Proxy target directory "%s" does not exist!',
                    $proxyTargetDir
                ),
                $e->getCode(),
                $e
            );
        }

        $this->setProxyTargetDir($proxyTargetDir);
        $this->setSessionBeanStore(new SerializableBeanStore());
        $this->setProxyWriterGenerator(new FileWriterGeneratorStrategy($proxyFileLocator));
    }

    /**
     * Sets the directory in which ProxyManager will store the generated proxy classes in.
     *
     * @param string $proxyTargetDir
     * @throws InvalidArgumentException
     */
    public function setProxyTargetDir(string $proxyTargetDir): void
    {
        if (!is_dir($proxyTargetDir)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Proxy target directory "%s" does not exist!',
                    $proxyTargetDir
                ),
                10
            );
        }

        if (!is_writable($proxyTargetDir)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Proxy target directory "%s" is not writable!',
                    $proxyTargetDir
                ),
                20
            );
        }

        $this->proxyTargetDir = $proxyTargetDir;
    }

    /**
     * Sets the {@link \ProxyManager\GeneratorStrategy\GeneratorStrategyInterface} which
     * ProxyManager will use the generate the proxy classes.
     *
     * @param GeneratorStrategyInterface $writergenerator
     */
    public function setProxyWriterGenerator(GeneratorStrategyInterface $writergenerator): void
    {
        $this->proxyWriterGenerator = $writergenerator;
    }

    /**
     * Sets the {@link \ProxyManager\Autoloader\AutoloaderInterface} that should be
     * used by ProxyManager to load the generated classes.
     *
     * @param AutoloaderInterface $autoloader
     * @throws \RuntimeException
     */
    public function setProxyAutoloader(AutoloaderInterface $autoloader): void
    {
        if ($this->proxyAutoloader instanceof AutoloaderInterface) {
            spl_autoload_unregister($this->proxyAutoloader);
        }

        $this->proxyAutoloader = $autoloader;

        spl_autoload_register($this->proxyAutoloader);
    }

    /**
     * Returns the ProxyManager configuration based on the current
     * {@link \bitExpert\Disco\BeanFactoryConfiguration}.
     *
     * @return Configuration
     */
    public function getProxyManagerConfiguration(): Configuration
    {
        $proxyManagerConfiguration = new Configuration();
        $proxyManagerConfiguration->setProxiesTargetDir($this->proxyTargetDir);

        if ($this->proxyWriterGenerator instanceof GeneratorStrategyInterface) {
            $proxyManagerConfiguration->setGeneratorStrategy($this->proxyWriterGenerator);
        }

        if ($this->proxyAutoloader instanceof AutoloaderInterface) {
            $proxyManagerConfiguration->setProxyAutoloader($this->proxyAutoloader);
        }

        return $proxyManagerConfiguration;
    }

    /**
     * Returns the configured {@link \bitExpert\Disco\Store\BeanStore} used to store
     * the session-aware beans in.
     *
     * @return BeanStore
     */
    public function getSessionBeanStore(): BeanStore
    {
        return $this->sessionBeanStore;
    }

    /**
     * Sets the {@link \bitExpert\Disco\Store\BeanStore} instance used to store the
     * session-aware beans.
     *
     * @param BeanStore $sessionBeanStore
     */
    public function setSessionBeanStore(BeanStore $sessionBeanStore): void
    {
        $this->sessionBeanStore = $sessionBeanStore;
    }
}
