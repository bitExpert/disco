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

namespace bitExpert\Disco\Store;

/**
 * The {@link \bitExpert\Disco\Store\SerializableBeanStore} contains all session-aware beans which
 * can be persisted in your preferable format.
 */
class SerializableBeanStore implements BeanStore
{
    /**
     * @var array
     */
    protected $beans;

    /**
     * Creates a new {@link \bitExpert\Disco\Store\SerializableBeanStore}.
     */
    public function __construct()
    {
        $this->beans = [];
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $beanId, $bean): void
    {
        $this->beans[$beanId] = $bean;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $beanId)
    {
        if (!isset($this->beans[$beanId])) {
            throw new \InvalidArgumentException(
                sprintf('Bean "%s" not defined in store!', $beanId)
            );
        }

        return $this->beans[$beanId];
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $beanId): bool
    {
        return isset($this->beans[$beanId]);
    }

    /**
     * {@inheritDoc}
     */
    public function __sleep()
    {
        return ['beans'];
    }
}
