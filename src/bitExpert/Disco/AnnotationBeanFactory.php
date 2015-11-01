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

use bitExpert\Disco\Proxy\Configuration\ConfigurationFactory;
use Doctrine\Common\Cache\Cache;
use Exception;

/**
 * BeanFactory implementation
 *
 * @api
 */
class AnnotationBeanFactory implements BeanFactory
{
    /**
     * @var string
     */
    protected $configClassName;
    /**
     * @var string[]
     */
    protected $parameters;
    /**
     * @var Cache
     */
    protected $cache;
    /**
     * @var object
     */
    protected $beanStore;

    /**
     * Creates a new {@link \bitExpert\Disco\BeanFactory}.
     *
     * @param $configClassName string
     * @param array $parameters
     * @param Cache $cache
     */
    public function __construct($configClassName, array $parameters = [], Cache $cache = null)
    {
        $this->configClassName = $configClassName;
        $this->parameters = $parameters;
        $this->cache = $cache;

        $this->__wakeup();
    }

    /**
     * {@inheritDoc}
     * @throws BeanException
     * @throws BeanNotFoundException
     */
    public function get($id)
    {
        $instance = null;

        if ($this->has($id)) {
            try {
                $instance = call_user_func([$this->beanStore, $id]);
            } catch (Exception $e) {
                throw new BeanException($e->getMessage());
            }
        }

        if (null === $instance) {
            throw new BeanNotFoundException(sprintf('"%s" is not defined!', $id));
        }

        return $instance;
    }

    /**
     * {@inheritDoc}
     */
    public function has($id)
    {
        return method_exists($this->beanStore, $id);
    }

    /**
     * {@inheritDoc}
     */
    public function __sleep()
    {
        return ['configClassName', 'parameters', 'cache'];
    }

    /**
     * {@inheritDoc}
     */
    public function __wakeup()
    {
        $this->beanStore = $this->initBeanStore($this->configClassName, $this->parameters, $this->cache);
    }

    /**
     * Returns an instance of the given $configClassName.
     *
     * @param $configClassName
     * @param array $parameters
     * @param Cache $cache
     * @return object
     */
    protected function initBeanStore($configClassName, array $parameters, Cache $cache = null)
    {
        $configFactory = new ConfigurationFactory($cache);
        return $configFactory->createInstance($configClassName, $parameters);
    }
}
