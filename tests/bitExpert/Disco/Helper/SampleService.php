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

namespace bitExpert\Disco\Helper;

class SampleService implements SampleServiceInterface
{
    public $test;

    /**
     * Setter method for the $test property.
     *
     * @param mixed $test
     */
    public function setTest($test)
    {
        $this->test = $test;
    }
}
