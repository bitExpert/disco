<?php

/*
 * This file is part of the Disco package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bitExpert\Disco;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use ProxyManager\GeneratorStrategy\GeneratorStrategyInterface;

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
     * Creates a new {@link \bitExpert\Disco\BeanFactoryConfiguration}.
     *
     * @param string $proxyTargetDir
     * @param GeneratorStrategyInterface|null $proxyGeneratorStrategy
     * @param Cache|null $annotationCache
     * @throws \InvalidArgumentException
     */
    public function __construct(
        $proxyTargetDir,
        GeneratorStrategyInterface $proxyGeneratorStrategy = null,
        Cache $annotationCache = null
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

        $this->proxyTargetDir = $proxyTargetDir;
        $this->proxyGeneratorStrategy = $proxyGeneratorStrategy;
        $this->annotationCache = $annotationCache;
    }

    /**
     * Returns the directory in which the ProxyManager will store the generated proxy classes.
     *
     * @return string
     */
    public function getProxyTargetDir()
    {
        return $this->proxyTargetDir;
    }

    /**
     * Returns the {@link \ProxyManager\GeneratorStrategy\GeneratorStrategyInterface} which the
     * ProxyManager will use the generate the proxy classes.
     *
     * @return GeneratorStrategyInterface
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
    public function getAnnotationCache()
    {
        return $this->annotationCache;
    }
}
