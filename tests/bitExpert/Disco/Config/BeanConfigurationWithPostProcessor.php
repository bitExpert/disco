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
     * @return SampleServiceBeanPostProcessor
     */
    public function sampleServiceBeanPostProcessor()
    {
        return new SampleServiceBeanPostProcessor();
    }

    /**
     * @BeanPostProcessor
     * @return BeanFactoryPostProcessor
     */
    public function beanFactoryBeanPostProcessor()
    {
        return new BeanFactoryPostProcessor();
    }

    /**
     * @Bean
     * @return SampleService
     */
    public function nonSingletonNonLazyRequestBean()
    {
        return new SampleService();
    }

    /**
     * @Bean({"lazy"=true})
     * @return SampleService
     */
    public function nonSingletonLazyRequestBean()
    {
        return new SampleService();
    }

    /**
     * @Bean
     * @return BeanFactoryAwareService
     */
    public function beanFactoryAwareBean()
    {
        return new BeanFactoryAwareService();
    }
}
