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

class G
{
    /**
     * @var F
     */
    private $f;

    /**
     * Creates a new {@link \bitExpert\Techno\BenchHelper\G}.
     *
     * @param F $f
     */
    public function __construct(F $f)
    {
        $this->f = $f;
    }
}
