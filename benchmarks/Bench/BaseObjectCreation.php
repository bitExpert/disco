<?php
/*
 * This file is part of the Disco package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bitExpert\Disco\Bench;

use bitExpert\Disco\BeanFactory;

abstract class BaseObjectCreation
{
    /** @var BeanFactory */
    protected $disco;

    public function benchCreateSimple()
    {
        $this->disco->get('A');
    }

    public function benchCreateSimpleAliased()
    {
        $this->disco->get('mySimpleService');
    }

    public function benchCreateComplex()
    {
        $this->disco->get('J');
    }
}
