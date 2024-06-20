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
class BeanConfigurationWithParameterizedPostProcessor
{
    #[BeanPostProcessor]
    #[Parameter(name: 'test', key: 'test')]
    public function sampleServiceBeanPostProcessor($test = ''): ParameterizedSampleServiceBeanPostProcessor
    {
        return new ParameterizedSampleServiceBeanPostProcessor($test);
    }

    #[Bean]
    public function nonSingletonNonLazyRequestBean(): SampleService
    {
        return new SampleService();
    }
}
