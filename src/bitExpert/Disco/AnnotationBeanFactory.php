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

        $id = $this->normalizeBeanId($id);
        if ($this->beanIdExists($id)) {
            try {
                $instance = call_user_func([$this->beanStore, $id]);
            } catch (\Throwable $e) {
                $message = sprintf(
                    'Exception occured while instanciating "%s": %s',
                    $id,
                    $e->getMessage()
                );

                throw new BeanException($message, 0, $e);
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
        $id = $this->normalizeBeanId($id);
        return $this->beanIdExists($id);
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

    /**
     * Helper method to "normalize" bean identifiers. Since the bean identifier is basically a method name
     * of the config class we can only support a subset of characters, namely alphabetic characters, digits
     * and underscore.
     *
     * @param string $id
     * @return string
     */
    protected function normalizeBeanId($id)
    {
        // filter out all invalid characters
        $id = preg_replace('#[^a-zA-Z0-9_]#', '', $id);
        // prepend underscore when first character is neither an alphabetic character nor a underscore
        if (!preg_match('#^[a-zA-Z_]#', $id)) {
            $id = '_' . $id;
        }

        return $id;
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise. Expects $id to be normalized!
     *
     * @param string $id Identifier of the entry to look for.
     * @return boolean
     */
    protected function beanIdExists($id)
    {
        if (empty($id) or !is_string($id)) {
            return false;
        }

        return is_callable([$this->beanStore, $id]);
    }
}
