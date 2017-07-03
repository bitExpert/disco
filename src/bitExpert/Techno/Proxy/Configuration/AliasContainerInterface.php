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

namespace bitExpert\Techno\Proxy\Configuration;

use bitExpert\Techno\BeanException;
use bitExpert\Techno\BeanNotFoundException;

/**
 * Interface similar to {@link \Psr\Container\ContainerInterface}. The interface is needed
 * to be able to retrieve aliased beans from the generated configuration class.
 */
interface AliasContainerInterface
{
    /**
     * Finds an entry of the container by the given alias and returns it.
     *
     * @param string $alias Alias of the entry to look for.
     * @return mixed
     * @throws BeanNotFoundException  No entry was found for this alias.
     * @throws BeanException Error while retrieving the entry.
     */
    public function getAlias(string $alias);

    /**
     * Returns true if the container can return an entry for the given alias.
     * Returns false otherwise.
     *
     * @param string $alias Identifier of the entry to look for
     * @return boolean
     */
    public function hasAlias(string $alias): bool;
}
