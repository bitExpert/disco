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

namespace bitExpert\Techno\BenchHelper;

class B
{
    /**
     * @var A
     */
    private $a;

    /**
     * Creates a new {@link \bitExpert\Techno\BenchHelper\B}.
     *
     * @param A $a
     */
    public function __construct(A $a)
    {
        $this->a = $a;
    }
}
