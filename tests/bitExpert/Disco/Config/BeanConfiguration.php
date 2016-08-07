<?php

/*
 * This file is part of the Disco package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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
     * @Bean({"singleton"=false, "lazy"=false, "scope"="request"})
     * @return SampleService
     */
    public function nonSingletonNonLazyRequestBean()
    {
        return new SampleService();
    }

    /**
     * @Bean({"singleton"=false, "lazy"=false, "scope"="session"})
     * @return MasterService
     */
    public function nonSingletonNonLazySessionBean()
    {
        return new MasterService($this->nonSingletonNonLazyRequestBean());
    }

    /**
     * @Bean({"singleton"=false, "lazy"=true, "scope"="request"})
     * @return SampleService
     */
    public function nonSingletonLazyRequestBean()
    {
        return new SampleService();
    }

    /**
     * @Bean({"singleton"=false, "lazy"=true, "scope"="session"})
     * @return MasterService
     */
    public function nonSingletonLazySessionBean()
    {
        return new MasterService($this->nonSingletonLazyRequestBean());
    }

    /**
     * @Bean({"singleton"=true, "lazy"=false, "scope"="request"})
     * @return SampleService
     */
    public function singletonNonLazyRequestBean()
    {
        return new SampleService();
    }

    /**
     * @Bean({"singleton"=true, "lazy"=false, "scope"="session"})
     * @return MasterService
     */
    public function singletonNonLazySessionBean()
    {
        return new MasterService($this->singletonNonLazyRequestBean());
    }

    /**
     * @Bean({"singleton"=true, "lazy"=true, "scope"="request"})
     * @return SampleService
     */
    public function singletonLazyRequestBean()
    {
        return new SampleService();
    }

    /**
     * @Bean({"singleton"=true, "lazy"=true, "scope"="session"})
     * @return MasterService
     */
    public function singletonLazySessionBean()
    {
        return new MasterService($this->singletonLazyRequestBean());
    }

    /**
     * @Bean({"singleton"=true, "lazy"=false, "scope"="request"})
     * @return InitializedService
     */
    public function singletonInitializedService()
    {
        return new InitializedService();
    }

    /**
     * @Bean({"singleton"=true, "lazy"=true, "scope"="request"})
     * @return InitializedService
     */
    public function singletonLazyInitializedService()
    {
        return new InitializedService();
    }
}
