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
use bitExpert\Disco\Helper\MasterService;
use bitExpert\Disco\Helper\SampleService;

/**
 * @Configuration
 */
class BeanConfigurationWithProtectedMethod
{
    /**
     * @Bean({"singleton"=true})
     * @return SampleService
     */
    protected function singletonDependency()
    {
        return new SampleService();
    }

    /**
     * @Bean({"singleton"=false})
     * @return MasterService
     */
    public function masterServiceWithSingletonDependency()
    {
        return new MasterService($this->singletonDependency());
    }

    /**
     * @Bean({"singleton"=false})
     * @return SampleService
     */
    protected function nonSingletonDependency()
    {
        return new SampleService();
    }

    /**
     * @Bean({"singleton"=false})
     * @return MasterService
     */
    public function masterServiceWithNonSingletonDependency()
    {
        return new MasterService($this->nonSingletonDependency());
    }
}
