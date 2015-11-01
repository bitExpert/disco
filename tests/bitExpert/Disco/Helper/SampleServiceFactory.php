<?php

/*
 * This file is part of the Disco package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bitExpert\Disco\Helper;

use bitExpert\Disco\FactoryBean;

class SampleServiceFactory implements FactoryBean
{
    /**
     * {@inheritDoc}
     */
    public function getObject()
    {
        return new SampleService();
    }
}
