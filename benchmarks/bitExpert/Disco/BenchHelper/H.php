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

class H
{
    /**
     * @var G
     */
    private $g;

    /**
     * Creates a new {@link \bitExpert\Disco\BenchHelper\H}.
     *
     * @param G $g
     */
    public function __construct(G $g)
    {
        $this->g = $g;
    }
}
