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

namespace bitExpert\Techno\Config;

use bitExpert\Techno\Annotations\Bean;
use bitExpert\Techno\Annotations\Configuration;
use bitExpert\Techno\Helper\SampleService;

/**
 * @Configuration
 */
class MissingReturnTypeConfiguration
{
    /**
     * @Bean({"singleton"=false, "lazy"=false, "scope"="request"})
     */
    public function nonSingletonNonLazyRequestBean()
    {
        return new SampleService();
    }
}
