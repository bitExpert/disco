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
class BeanConfigurationWithProtectedMethod
{
    /**
     * @Bean({"singleton"=false})
     */
    public function masterServiceWithSingletonDependency(): MasterService
    {
        return new MasterService($this->singletonDependency());
    }

    /**
     * @Bean({"singleton"=true})
     */
    protected function singletonDependency(): SampleService
    {
        return new SampleService();
    }

    /**
     * @Bean({"singleton"=false})
     */
    public function masterServiceWithNonSingletonDependency(): MasterService
    {
        return new MasterService($this->nonSingletonDependency());
    }

    /**
     * @Bean({"singleton"=false})
     */
    protected function nonSingletonDependency(): SampleService
    {
        return new SampleService();
    }
}
