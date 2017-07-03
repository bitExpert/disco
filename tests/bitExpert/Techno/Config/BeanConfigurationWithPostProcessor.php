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
use bitExpert\Techno\Annotations\BeanPostProcessor;
use bitExpert\Techno\Annotations\Configuration;
use bitExpert\Techno\BeanFactoryPostProcessor;
use bitExpert\Techno\Helper\BeanFactoryAwareService;
use bitExpert\Techno\Helper\SampleService;
use bitExpert\Techno\Helper\SampleServiceBeanPostProcessor;

/**
 * @Configuration
 */
class BeanConfigurationWithPostProcessor
{
    /**
     * @BeanPostProcessor
     */
    public function sampleServiceBeanPostProcessor(): SampleServiceBeanPostProcessor
    {
        return new SampleServiceBeanPostProcessor();
    }

    /**
     * @Bean
     */
    public function nonSingletonNonLazyRequestBean(): SampleService
    {
        return new SampleService();
    }

    /**
     * @Bean({"lazy"=true})
     */
    public function nonSingletonLazyRequestBean(): SampleService
    {
        return new SampleService();
    }
}
