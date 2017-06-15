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
use bitExpert\Disco\Annotations\BeanPostProcessor;
use bitExpert\Disco\Annotations\Configuration;
use bitExpert\Disco\BeanFactoryPostProcessor;
use bitExpert\Disco\Helper\BeanFactoryAwareService;
use bitExpert\Disco\Helper\SampleService;
use bitExpert\Disco\Helper\SampleServiceBeanPostProcessor;

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
