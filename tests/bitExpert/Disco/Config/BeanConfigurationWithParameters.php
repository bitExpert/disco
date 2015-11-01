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
use bitExpert\Disco\Annotations\Parameter;
use bitExpert\Disco\Annotations\Parameters;
use bitExpert\Disco\Helper\SampleService;

/**
 * @Configuration
 */
class BeanConfigurationWithParameters
{
    /**
     * @Bean({"singleton"=false})
     * @Parameters({
     *  @Parameter({"name" = "test"})
     * })
     * @return SampleService
     */
    public function sampleServiceWithParam($test)
    {
        $service = new SampleService();
        $service->setTest($test);
        return $service;
    }

    /**
     * @Bean({"singleton"=false})
     * @Parameters({
     *  @Parameter({"name" = "test.nested.key"})
     * })
     * @return SampleService
     */
    public function sampleServiceWithNestedParamKey($test)
    {
        $service = new SampleService();
        $service->setTest($test);
        return $service;
    }

    /**
     * @Bean({"singleton"=false})
     * @Parameters({
     *  @Parameter({"name" = "test", "default" = "myDefaultValue"})
     * })
     * @return SampleService
     */
    public function sampleServiceWithParamDefaultValue($test)
    {
        $service = new SampleService();
        $service->setTest($test);
        return $service;
    }

    /**
     * @Bean({"singleton"=false})
     * @Parameters({
     *  @Parameter({"name" = "test", "required" = false})
     * })
     * @return SampleService
     */
    public function sampleServiceWithoutRequiredParam($test)
    {
        $service = new SampleService();
        $service->setTest($test);
        return $service;
    }
}
