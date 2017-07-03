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
use bitExpert\Techno\Annotations\Parameter;
use bitExpert\Techno\Helper\ParameterizedSampleServiceBeanPostProcessor;
use bitExpert\Techno\Helper\SampleService;

/**
 * @Configuration
 */
class BeanConfigurationWithParameterizedPostProcessor
{
    /**
     * @BeanPostProcessor({
     *   "parameters"={
     *      @Parameter({"name" = "test"})
     *   }
     * })
     */
    public function sampleServiceBeanPostProcessor($test = ''): ParameterizedSampleServiceBeanPostProcessor
    {
        return new ParameterizedSampleServiceBeanPostProcessor($test);
    }

    /**
     * @Bean
     */
    public function nonSingletonNonLazyRequestBean(): SampleService
    {
        return new SampleService();
    }
}
