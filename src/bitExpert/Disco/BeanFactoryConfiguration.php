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

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use ProxyManager\Autoloader\Autoloader;
use ProxyManager\Autoloader\AutoloaderInterface;
use ProxyManager\Configuration;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use ProxyManager\GeneratorStrategy\GeneratorStrategyInterface;
use ProxyManager\Inflector\ClassNameInflector;

/**
 * BeanFactory configuration class.
 *
 * @api
 */
class BeanFactoryConfiguration
{
    /**
     * @var string
     */
    protected $proxyTargetDir;
    /**
     * @var GeneratorStrategyInterface
     */
    protected $proxyGeneratorStrategy;
    /**
     * @var Cache
     */
    protected $annotationCache;
    /**
     * @var AutoloaderInterface
     */
    protected $proxyAutoloader;

    /**
     * Creates a new {@link \bitExpert\Disco\BeanFactoryConfiguration}.
     *
     * @param string $proxyTargetDir
     * @param GeneratorStrategyInterface|null $proxyGeneratorStrategy
     * @param Cache|null $annotationCache
     * @param AutoloaderInterface $proxyAutoloader
     * @throws \InvalidArgumentException
     */
    public function __construct(
        $proxyTargetDir,
        GeneratorStrategyInterface $proxyGeneratorStrategy = null,
        Cache $annotationCache = null,
        AutoloaderInterface $proxyAutoloader = null
    ) {
        if (!is_dir($proxyTargetDir)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Proxy target directory "%s" does not exist!',
                    $proxyTargetDir
                )
            );
        }

        if (!is_writable($proxyTargetDir)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Proxy target directory "%s" is not writeable!',
                    $proxyTargetDir
                )
            );
        }

        if ($annotationCache === null) {
            $annotationCache = new FilesystemCache($proxyTargetDir);
        }

        if ($proxyAutoloader !== null) {
            spl_autoload_register($proxyAutoloader);
        }

        $this->proxyTargetDir = $proxyTargetDir;
        $this->proxyGeneratorStrategy = $proxyGeneratorStrategy;
        $this->annotationCache = $annotationCache;
        $this->proxyAutoloader = $proxyAutoloader;
    }

    /**
     * Returns the directory in which the ProxyManager will store the generated proxy classes.
     *
     * @return string
     */
    public function getProxyTargetDir() : string
    {
        return $this->proxyTargetDir;
    }

    /**
     * Returns the {@link \ProxyManager\GeneratorStrategy\GeneratorStrategyInterface} which the
     * ProxyManager will use the generate the proxy classes.
     *
     * @return GeneratorStrategyInterface : null
     */
    public function getProxyGeneratorStrategy()
    {
        return $this->proxyGeneratorStrategy;
    }

    /**
     * Returns the configured {@link \Doctrine\Common\Cache\Cache} to store the parsed
     * annotation metadata in.
     *
     * @return Cache
     */
    public function getAnnotationCache() : Cache
    {
        return $this->annotationCache;
    }

    /**
     * Returns the {@link \ProxyManager\Autoloader\AutoloaderInterface} that should be
     * used by ProxyManager to load the generated classes.
     *
     * @return AutoloaderInterface|null
     */
    public function getProxyAutoloader()
    {
        return $this->proxyAutoloader;
    }

    /**
     * Factory method to create a default {@link \Disco\BeanFactoryConfiguration} by simply
     * providing a $cacheDir.
     *
     * @param string $cacheDir
     * @return BeanFactoryConfiguration
     * @throws \InvalidArgumentException
     */
    public static function getDefault(string $cacheDir) : self
    {
        try {
            $annotationCache = new FilesystemCache($cacheDir);
            $proxyFileLocator = new FileLocator($cacheDir);
            $proxyWriterGenerator = new FileWriterGeneratorStrategy($proxyFileLocator);
            $proxyAutoloader = new Autoloader(
                $proxyFileLocator,
                new ClassNameInflector(Configuration::DEFAULT_PROXY_NAMESPACE)
            );

            return new self($cacheDir, $proxyWriterGenerator, $annotationCache, $proxyAutoloader);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(
                sprintf('The directory "%s" does not exist or is not writeable!', $cacheDir),
                0,
                $e
            );
        }
    }
}
