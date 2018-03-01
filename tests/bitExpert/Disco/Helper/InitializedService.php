<?php

/*
 * This file is part of the 02003-bitExpertLabs-24-Disco package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace bitExpert\Disco\Helper;

use bitExpert\Disco\InitializedBean;

class InitializedService implements InitializedBean
{
    /**
     * Counts how often postInitialization() gets invoked.
     *
     * @var int
     */
    public $postInitCnt = 0;

    /**
     * {@inheritDoc}
     */
    public function postInitialization(): void
    {
        $this->postInitCnt++;
    }
}
