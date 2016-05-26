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
use Exception;

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
     * @var object
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

        if ($this->has($id)) {
            try {
                $instance = call_user_func([$this->beanStore, $id]);
            } catch (Exception $e) {
                $message = sprintf(
                    'Exception occured while instanciating "%s": %s',
                    $id,
                    $e->getMessage()
                );

                throw new BeanException($message, null, $e);
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
        return is_callable([$this->beanStore, $id]);
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
