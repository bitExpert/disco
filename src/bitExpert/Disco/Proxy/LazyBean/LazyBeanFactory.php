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

namespace bitExpert\Disco\Proxy\LazyBean;

use Closure;
use ProxyManager\Configuration;
use ProxyManager\Factory\AbstractBaseFactory;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;

/**
 * Factory responsible of producing virtual proxy instances.
 */
class LazyBeanFactory extends AbstractBaseFactory
{
    /**
     * @var LazyBeanGenerator
     */
    private $generator;
    /**
     * @var string
     */
    private $beanId;

    /**
     * Creates a new {@link \bitExpert\Disco\Proxy\LazyBean\LazyBeanFactory}.
     *
     * @param string $beanId
     * @param \ProxyManager\Configuration $configuration
     */
    public function __construct($beanId, Configuration $configuration = null)
    {
        parent::__construct($configuration);

        $this->beanId = $beanId;
    }

    /**
     * @param string  $className
     * @param Closure $initializer
     *
     * @return object
     */
    public function createProxy(string $className, Closure $initializer): object
    {
        $proxyClassName = $this->generateProxy($className);

        return new $proxyClassName($this->beanId, $initializer);
    }

    /**
     * {@inheritDoc}
     */
    protected function getGenerator(): ProxyGeneratorInterface
    {
        return $this->generator ?: $this->generator = new LazyBeanGenerator();
    }
}
