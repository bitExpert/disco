<?php

/*
 * This file is part of the Techno package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace bitExpert\Techno;

/**
 * Global registry for the configured {@link \bitExpert\Techno\BeanFactory} instance.
 *
 * @api
 */
class BeanFactoryRegistry
{
    /**
     * @var BeanFactory
     */
    protected static $beanFactory = null;

    /**
     * Registers a {@link \bitExpert\Techno\BeanFactory} instance in the registry to make the instance
     * globally available.
     *
     * @param BeanFactory $beanFactory
     */
    public static function register(BeanFactory $beanFactory): void
    {
        self::$beanFactory = $beanFactory;
    }

    /**
     * Returns the registered {@link \bitExpert\Techno\BeanFactory} instance or null if not defined.
     *
     * @return BeanFactory|null
     */
    public static function getInstance(): ?BeanFactory
    {
        return self::$beanFactory;
    }
}
