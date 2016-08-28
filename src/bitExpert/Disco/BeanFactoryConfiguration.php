<?php

/*
 * This file is part of the Disco package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types = 1);

namespace bitExpert\Disco;

use bitExpert\Disco\Store\BeanStore;
use bitExpert\Disco\Store\SerializableBeanStore;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use InvalidArgumentException;
use ProxyManager\Autoloader\AutoloaderInterface;
use ProxyManager\Configuration;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use ProxyManager\GeneratorStrategy\GeneratorStrategyInterface;
use RuntimeException;

/**
 * BeanFactory configuration class.
 *
 * @api
 */
class BeanFactoryConfiguration
{
    /**
     * @var Cache
     */
    protected $annotationCache;
    /**
     * @var BeanStore
     */
    protected $beanStore;
    /**
     * @var string
     */
    protected $proxyTargetDir;
    /**
     * @var GeneratorStrategyInterface
     */
    protected $proxyGeneratorStrategy;
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
        $this->setAnnotationCache(new FilesystemCache($proxyTargetDir));
        $this->setBeanStore(new SerializableBeanStore());
        $this->setProxyWriterGenerator(new FileWriterGeneratorStrategy($proxyFileLocator));
    }

    /**
     * Sets the {@link \Doctrine\Common\Cache\Cache} to store the parsed annotation
     * metadata in.
     *
     * @param Cache $annotationCache
     */
    public function setAnnotationCache(Cache $annotationCache)
    {
        $this->annotationCache = $annotationCache;
    }

    /**
     * Sets the {@link \bitExpert\Disco\Store\BeanStore} instance used to store the
     * session-aware beans.
     *
     * @param BeanStore $beanStore
     */
    public function setBeanStore(BeanStore $beanStore)
    {
        $this->beanStore = $beanStore;
    }

    /**
     * Sets the directory in which ProxyManager will store the generated proxy classes in.
     *
     * @param string $proxyTargetDir
     * @throws InvalidArgumentException
     */
    public function setProxyTargetDir(string $proxyTargetDir)
    {
        if (!is_dir($proxyTargetDir)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Proxy target directory "%s" does not exist!',
                    $proxyTargetDir
                )
            );
        }

        if (!is_writable($proxyTargetDir)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Proxy target directory "%s" is not writeable!',
                    $proxyTargetDir
                )
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
    public function setProxyWriterGenerator(GeneratorStrategyInterface $writergenerator)
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
    public function setProxyAutoloader(AutoloaderInterface $autoloader)
    {
        if ($this->proxyAutoloader instanceof AutoloaderInterface) {
            if (!spl_autoload_unregister($this->proxyAutoloader)) {
                throw new RuntimeException(
                    sprintf('Cannot unregister autoloader "%s"', get_class($this->proxyAutoloader))
                );
            }
        }

        $this->proxyAutoloader = $autoloader;

        if (!spl_autoload_register($this->proxyAutoloader, false)) {
            throw new RuntimeException(
                sprintf('Cannot register autoloader "%s"', get_class($this->proxyAutoloader))
            );
        }
    }

    /**
     * Returns the ProxyManager configuration based on the current
     * {@link \bitExpert\Disco\BeanFactoryConfiguration}.
     *
     * @return Configuration
     */
    public function getProxyManagerConfiguration() : Configuration
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
     * Returns the configured {@link \Doctrine\Common\Cache\Cache} used to store
     * the parsed annotation metadata in.
     *
     * @return Cache
     */
    public function getAnnotationCache() : Cache
    {
        return $this->annotationCache;
    }

    /**
     * Returns the configured {@link \bitExpert\Disco\Store\BeanStore} used to store
     * the session-aware beans in.
     *
     * @return BeanStore
     */
    public function getBeanStore() : BeanStore
    {
        return $this->beanStore;
    }
}
