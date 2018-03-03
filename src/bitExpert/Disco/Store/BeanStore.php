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

use InvalidArgumentException;

interface BeanStore
{
    /**
     * Adds given $bean instance (or primitive) to the bean store by the given $beanId.
     *
     * @param string $beanId
     * @param mixed $bean
     */
    public function add(string $beanId, $bean): void;

    /**
     * Retrieves bean instance for $beanId.
     *
     * @param string $beanId
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function get(string $beanId);

    /**
     * Checks if a bean instance for $beanId exists. Will return true if an instance
     * exists and false if no instance can be found.
     *
     * @param string $beanId
     * @return bool
     */
    public function has(string $beanId): bool;
}
