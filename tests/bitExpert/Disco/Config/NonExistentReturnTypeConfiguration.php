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

namespace bitExpert\Disco\Config;

use bitExpert\Disco\Attributes\Bean;
use bitExpert\Disco\Attributes\Configuration;
use bitExpert\Disco\Helper\SampleService;

#[Configuration]
class NonExistentReturnTypeConfiguration
{
    #[Bean(singleton: false, lazy: false, scope: Bean::SCOPE_REQUEST)]
    public function nonSingletonNonLazyRequestBean(): \MyOtherClass
    {
        return new SampleService();
    }
}
