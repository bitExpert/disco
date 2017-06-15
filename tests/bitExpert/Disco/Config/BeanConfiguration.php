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
use bitExpert\Disco\Helper\InitializedService;
use bitExpert\Disco\Helper\MasterService;
use bitExpert\Disco\Helper\SampleService;

/**
 * @Configuration
 */
class BeanConfiguration
{
    /**
     * @Bean({"singleton"=false, "lazy"=false, "scope"="session"})
     */
    public function nonSingletonNonLazySessionBean(): MasterService
    {
        return new MasterService($this->nonSingletonNonLazyRequestBean());
    }

    /**
     * @Bean({"singleton"=false, "lazy"=false, "scope"="request"})
     */
    public function nonSingletonNonLazyRequestBean(): SampleService
    {
        return new SampleService();
    }

    /**
     * @Bean({"singleton"=false, "lazy"=true, "scope"="session"})
     */
    public function nonSingletonLazySessionBean(): MasterService
    {
        return new MasterService($this->nonSingletonLazyRequestBean());
    }

    /**
     * @Bean({"singleton"=false, "lazy"=true, "scope"="request"})
     */
    public function nonSingletonLazyRequestBean(): SampleService
    {
        return new SampleService();
    }

    /**
     * @Bean({"singleton"=true, "lazy"=false, "scope"="session"})
     */
    public function singletonNonLazySessionBean(): MasterService
    {
        return new MasterService($this->singletonNonLazyRequestBean());
    }

    /**
     * @Bean({"singleton"=true, "lazy"=false, "scope"="request"})
     */
    public function singletonNonLazyRequestBean(): SampleService
    {
        return new SampleService();
    }

    /**
     * @Bean({"singleton"=true, "lazy"=true, "scope"="session"})
     */
    public function singletonLazySessionBean(): MasterService
    {
        return new MasterService($this->singletonLazyRequestBean());
    }

    /**
     * @Bean({"singleton"=true, "lazy"=true, "scope"="request"})
     */
    public function singletonLazyRequestBean(): SampleService
    {
        return new SampleService();
    }

    /**
     * @Bean({"singleton"=true, "lazy"=false, "scope"="request"})
     */
    public function singletonInitializedService(): InitializedService
    {
        return new InitializedService();
    }

    /**
     * @Bean({"singleton"=true, "lazy"=true, "scope"="request"})
     */
    public function singletonLazyInitializedService(): InitializedService
    {
        return new InitializedService();
    }
}
