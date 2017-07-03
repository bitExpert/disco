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

class C
{
    /**
     * @var B
     */
    private $b;

    /**
     * Creates a new {@link \bitExpert\Techno\BenchHelper\C}.
     *
     * @param B $b
     */
    public function __construct(B $b)
    {
        $this->b = $b;
    }
}
