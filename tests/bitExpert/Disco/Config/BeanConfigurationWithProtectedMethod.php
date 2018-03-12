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

    protected function nonDiRelevantFunction()
    {
        // Empty on purpose
    }
}
