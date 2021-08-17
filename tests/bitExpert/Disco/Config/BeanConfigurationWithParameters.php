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

#[Configuration]
class BeanConfigurationWithParameters
{
    #[Bean(singleton: false)]
    #[Parameter(name: 'test', key: 'configKey')]
    public function sampleServiceWithParam($test = ''): SampleService
    {
        $service = new SampleService();
        $service->setTest($test);
        return $service;
    }

    #[Bean(singleton: false)]
    #[Parameter('configKey1')]
    #[Parameter('configKey2')]
    public function sampleServiceWithPositionalParams($test = '', $anotherTest = ''): SampleService
    {
        $service = new SampleService();
        $service->setTest($test);
        $service->setAnotherTest($anotherTest);
        return $service;
    }

    #[Bean(singleton: false)]
    #[Parameter(key: 'configKey2', name: 'anotherTest')]
    #[Parameter(key: 'configKey1', name: 'test')]
    public function sampleServiceWithNamedParams($test = '', $anotherTest = ''): SampleService
    {
        $service = new SampleService();
        $service->setTest($test);
        $service->setAnotherTest($anotherTest);
        return $service;
    }

    #[Bean(singleton: false)]
    #[Parameter(key: 'configKey2', name: 'anotherTest')]
    #[Parameter(key: 'configKey1')]
    public function sampleServiceWithMixedPositionalAndNamedParams($test = '', $anotherTest = ''): SampleService
    {
        $service = new SampleService();
        $service->setTest($test);
        $service->setAnotherTest($anotherTest);
        return $service;
    }

    #[Bean(singleton: false)]
    #[Parameter(name: 'test', key: 'configKey', default: null)]
    public function sampleServiceWithParamNull($test = ''): SampleService
    {
        $service = new SampleService();
        $service->setTest($test);
        return $service;
    }

    #[Bean(singleton: false)]
    #[Parameter(name: 'test', key: 'configKey', default: true)]
    public function sampleServiceWithParamBool($test = ''): SampleService
    {
        $service = new SampleService();
        $service->setTest($test);
        return $service;
    }

    #[Bean(singleton: false)]
    #[Parameter(name: 'test', key: 'configKey', default: 0)]
    public function sampleServiceWithParamEmpty($test = ''): SampleService
    {
        $service = new SampleService();
        $service->setTest($test);
        return $service;
    }

    #[Bean(singleton: false)]
    #[Parameter(name: 'test', key: 'config.nested.key')]
    public function sampleServiceWithNestedParamKey($test = ''): SampleService
    {
        $service = new SampleService();
        $service->setTest($test);
        return $service;
    }

    #[Bean(singleton: false)]
    #[Parameter(name: 'test', key: 'configKey', default: 'myDefaultValue')]
    public function sampleServiceWithParamDefaultValue($test = ''): SampleService
    {
        $service = new SampleService();
        $service->setTest($test);
        return $service;
    }

    #[Bean(singleton: false)]
    #[Parameter(name: 'test', key: 'configKey', required: false)]
    public function sampleServiceWithoutRequiredParam($test = ''): SampleService
    {
        $service = new SampleService();
        $service->setTest($test);
        return $service;
    }
}
