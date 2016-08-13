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

use bitExpert\Disco\Annotations\Bean;
use bitExpert\Disco\Annotations\Configuration;
use bitExpert\Disco\Helper\MasterService;
use bitExpert\Disco\Helper\SampleService;

/**
 * @Configuration
 */
class WrongReturnTypeConfiguration
{
    /**
     * @Bean({"singleton"=false, "lazy"=false, "scope"="request"})
     * @return SampleService
     */
    public function nonLazyBeanNotReturningAnything()
    {
    }

    /**
     * @Bean({"singleton"=false, "lazy"=false, "scope"="request"})
     * @return SampleService
     */
    public function nonLazyBeanReturningSomethingWrong()
    {
        return new MasterService(new SampleService());
    }

    /**
     * @Bean({"singleton"=false, "lazy"=true, "scope"="request"})
     * @return SampleService
     */
    public function lazyBeanNotReturningAnything()
    {
    }

    /**
     * @Bean({"singleton"=false, "lazy"=true, "scope"="request"})
     * @return SampleService
     */
    public function lazyBeanReturningSomethingWrong()
    {
        return new MasterService(new SampleService());
    }
}
