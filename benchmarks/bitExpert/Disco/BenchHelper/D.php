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

namespace bitExpert\Disco\BenchHelper;

class D
{
    /**
     * @var C
     */
    private $c;

    /**
     * Creates a new {@link \bitExpert\Disco\BenchHelper\D}.
     *
     * @param C $c
     */
    public function __construct(C $c)
    {
        $this->c = $c;
    }
}
