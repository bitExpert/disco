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
use bitExpert\Techno\Helper\MasterService;
use bitExpert\Techno\Helper\SampleService;

/**
 * @Configuration
 */
class WrongReturnTypeConfiguration
{
    /**
     * @Bean({"singleton"=false, "lazy"=false, "scope"="request"})
     */
    public function nonLazyBeanNotReturningAnything(): SampleService
    {
    }

    /**
     * @Bean({"singleton"=false, "lazy"=false, "scope"="request"})
     */
    public function nonLazyBeanReturningSomethingWrong(): SampleService
    {
        return new MasterService(new SampleService());
    }

    /**
     * @Bean({"singleton"=false, "lazy"=true, "scope"="request"})
     */
    public function lazyBeanNotReturningAnything(): SampleService
    {
    }

    /**
     * @Bean({"singleton"=false, "lazy"=true, "scope"="request"})
     */
    public function lazyBeanReturningSomethingWrong(): SampleService
    {
        return new MasterService(new SampleService());
    }
}
