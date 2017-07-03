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

class F
{
    /**
     * @var E
     */
    private $e;

    /**
     * Creates a new {@link \bitExpert\Techno\BenchHelper\F}.
     *
     * @param E $e
     */
    public function __construct(E $e)
    {
        $this->e = $e;
    }
}
