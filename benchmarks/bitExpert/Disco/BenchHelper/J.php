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

class J
{
    /**
     * @var I
     */
    private $i;

    /**
     * Creates a new {@link \bitExpert\Techno\BenchHelper\J}.
     *
     * @param I $i
     */
    public function __construct(I $i)
    {
        $this->i = $i;
    }
}
