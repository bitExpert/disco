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

use bitExpert\Disco\Proxy\Configuration\AliasContainerInterface;
use bitExpert\Disco\Proxy\Configuration\ConfigurationFactory;

/**
 * {@link \bitExpert\Disco\BeanFactory} implementation.
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
     * @var BeanFactoryConfiguration
     */
    protected $config;
    /**
     * @var AliasContainerInterface
     */
    protected $beanStore;

    /**
     * Creates a new {@link \bitExpert\Disco\BeanFactory}.
     *
     * @param $configClassName string
     * @param array $parameters
     * @param BeanFactoryConfiguration $config
     */
    public function __construct($configClassName, array $parameters = [], BeanFactoryConfiguration $config = null)
    {
        $this->configClassName = $configClassName;
        $this->parameters = $parameters;
        $this->config = $config;

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

        try {
            if (is_callable([$this->beanStore, $id])) {
                $instance = $this->beanStore->$id();
            }

            if ($this->beanStore->hasAlias($id)) {
                $instance = $this->beanStore->getAlias($id);
            }
        } catch (\Throwable $e) {
            $message = sprintf(
                'Exception occured while instanciating "%s": %s',
                $id,
                $e->getMessage()
            );

            throw new BeanException($message, 0, $e);
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
        return is_callable([$this->beanStore, $id]) || $this->beanStore->hasAlias($id);
    }

    /**
     * {@inheritDoc}
     */
    public function __sleep()
    {
        return ['configClassName', 'parameters', 'config'];
    }

    /**
     * {@inheritDoc}
     */
    public function __wakeup()
    {
        $this->beanStore = $this->initBeanStore($this->configClassName, $this->parameters, $this->config);
    }

    /**
     * Returns an instance of the given $configClassName.
     *
     * @param $configClassName
     * @param array $parameters
     * @param BeanFactoryConfiguration $config
     * @return object
     */
    protected function initBeanStore($configClassName, array $parameters, BeanFactoryConfiguration $config = null)
    {
        $configFactory = new ConfigurationFactory($config);
        return $configFactory->createInstance($configClassName, $parameters);
    }
}
