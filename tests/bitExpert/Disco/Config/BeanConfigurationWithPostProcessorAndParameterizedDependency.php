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
use bitExpert\Disco\Annotations\Parameter;
use bitExpert\Disco\Helper\ParameterizedSampleServiceBeanPostProcessor;
use bitExpert\Disco\Helper\SampleService;

#[Configuration]
class BeanConfigurationWithPostProcessorAndParameterizedDependency
{
    #[BeanPostProcessor]
    public function sampleServiceBeanPostProcessor(): ParameterizedSampleServiceBeanPostProcessor
    {
        return new ParameterizedSampleServiceBeanPostProcessor($this->dependency());
    }

    #[Bean]
    #[Parameter(name: 'property1', key: 'configKey1')]
    #[Parameter(name: 'property2', key: 'configKey2')]
    public function dependency(string $property1 = '', string $property2 = ''): \stdClass
    {
        $object = new \stdClass();
        $object->property1 = $property1;
        $object->property2 = $property2;
        return $object;
    }

    #[Bean]
    public function nonSingletonNonLazyRequestBean(): SampleService
    {
        return new SampleService();
    }
}
