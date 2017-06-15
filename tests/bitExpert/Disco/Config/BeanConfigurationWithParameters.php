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
use bitExpert\Disco\Annotations\Parameter;
use bitExpert\Disco\Helper\SampleService;

/**
 * @Configuration
 */
class BeanConfigurationWithParameters
{
    /**
     * @Bean({
     *   "singleton"=false,
     *   "parameters"={
     *     @Parameter({"name" = "test"})
     *   }
     * })
     */
    public function sampleServiceWithParam($test = ''): SampleService
    {
        $service = new SampleService();
        $service->setTest($test);
        return $service;
    }

    /**
     * @Bean({
     *   "singleton"=false,
     *   "parameters"={
     *     @Parameter({"name" = "test", "default" = null})
     *   }
     * })
     */
    public function sampleServiceWithParamNull($test = ''): SampleService
    {
        $service = new SampleService();
        $service->setTest($test);
        return $service;
    }

    /**
     * @Bean({
     *   "singleton"=false,
     *   "parameters"={
     *     @Parameter({"name" = "test", "default" = true})
     *   }
     * })
     */
    public function sampleServiceWithParamBool($test = ''): SampleService
    {
        $service = new SampleService();
        $service->setTest($test);
        return $service;
    }

    /**
     * @Bean({
     *   "singleton"=false,
     *   "parameters"={
     *     @Parameter({"name" = "test", "default" = 0})
     *   }
     * })
     */
    public function sampleServiceWithParamEmpty($test = ''): SampleService
    {
        $service = new SampleService();
        $service->setTest($test);
        return $service;
    }

    /**
     * @Bean({
     *   "singleton"=false,
     *   "parameters"={
     *     @Parameter({"name" = "test.nested.key"})
     *   }
     * })
     */
    public function sampleServiceWithNestedParamKey($test = ''): SampleService
    {
        $service = new SampleService();
        $service->setTest($test);
        return $service;
    }

    /**
     * @Bean({
     *   "singleton"=false,
     *   "parameters"={
     *     @Parameter({"name" = "test", "default" = "myDefaultValue"})
     *   }
     * })
     */
    public function sampleServiceWithParamDefaultValue($test = ''): SampleService
    {
        $service = new SampleService();
        $service->setTest($test);
        return $service;
    }

    /**
     * @Bean({
     *   "singleton"=false,
     *   "parameters"={
     *     @Parameter({"name" = "test", "required" = false})
     *   }
     * })
     */
    public function sampleServiceWithoutRequiredParam($test = ''): SampleService
    {
        $service = new SampleService();
        $service->setTest($test);
        return $service;
    }
}
