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
     * @param class-string<Object> $configClassName
     * @param array<string, mixed> $parameters
     * @param BeanFactoryConfiguration|null $config
     */
    public function __construct(
        string                   $configClassName,
        array                    $parameters = [],
        BeanFactoryConfiguration $config = null
    ) {
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
    public function get(string $id)
    {
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
    public function has(string $id): bool
    {
        return is_callable([$this->beanConfig, $id]) || $this->beanConfig->hasAlias($id);
    }
}
