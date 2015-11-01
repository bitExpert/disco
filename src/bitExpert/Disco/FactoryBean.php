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

/**
 * A {@link \bitExpert\Disco\FactoryBean} allows to encapsulate complex instantiation
 * logic in a factory. It is a callback interface whose method is called after
 * the bean implementing this interface was instantiated.
 *
 * @api
 */
interface FactoryBean
{
    /**
     * Returns the object the factory creates. If you use a class that implements
     * this interface in configuration and call the get() method of the
     * {@link \bitExpert\Disco\BeanFactory} you will get the object returned by this
     * method, not the factory itself.
     *
     * @return object
     */
    public function getObject();
}
