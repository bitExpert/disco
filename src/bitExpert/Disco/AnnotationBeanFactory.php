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
     * @var AliasContainerInterface
     */
    protected $beanConfig;

    /**
     * Creates a new {@link \bitExpert\Disco\BeanFactory}.
     *
     * @param $configClassName string
     * @param array $parameters
     * @param BeanFactoryConfiguration $config
     */
    public function __construct($configClassName, array $parameters = [], BeanFactoryConfiguration $config = null)
    {
        if ($config === null) {
            $config = new BeanFactoryConfiguration(sys_get_temp_dir());
        }

        $configFactory = new ConfigurationFactory($config);
        $this->beanConfig = $configFactory->createInstance($config, $configClassName, $parameters);
    }

    /**
     * {@inheritDoc}
     * @throws BeanException
     * @throws BeanNotFoundException
     */
    public function get($id)
    {
        if (!is_string($id) || empty($id)) {
            throw new BeanException('Id must be a non-empty string.');
        }

        $instance = null;

        try {
            if (is_callable([$this->beanConfig, $id])) {
                $instance = $this->beanConfig->$id();
            } elseif ($this->beanConfig->hasAlias($id)) {
                $instance = $this->beanConfig->getAlias($id);
            }
        } catch (\Throwable $e) {
            $message = sprintf(
                'Exception occurred while instantiating "%s": %s',
                $id,
                $e->getMessage()
            );

            throw new BeanException($message, $e->getCode(), $e);
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
        if (!is_string($id) || empty($id)) {
            return false;
        }

        return is_callable([$this->beanConfig, $id]) || $this->beanConfig->hasAlias($id);
    }
}
