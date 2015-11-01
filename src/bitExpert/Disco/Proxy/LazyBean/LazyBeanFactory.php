<?php

/*
 * This file is part of the Disco package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bitExpert\Disco\Proxy\LazyBean;

use Closure;
use ProxyManager\Factory\AbstractLazyFactory;
use ProxyManager\Configuration;

/**
 * Factory responsible of producing virtual proxy instances.
 */
class LazyBeanFactory extends AbstractLazyFactory
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
     * {@inheritDoc}
     */
    protected function getGenerator()
    {
        return $this->generator ?: $this->generator = new LazyBeanGenerator();
    }

    /**
     * {@inheritDoc}
     */
    public function createProxy($className, Closure $initializer)
    {
        $proxyClassName = $this->generateProxy($className);

        return new $proxyClassName($this->beanId, $initializer);
    }
}
