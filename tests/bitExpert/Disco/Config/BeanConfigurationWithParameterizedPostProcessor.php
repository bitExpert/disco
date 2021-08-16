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

use bitExpert\Disco\Attributes\Bean;
use bitExpert\Disco\Attributes\BeanPostProcessor;
use bitExpert\Disco\Attributes\Configuration;
use bitExpert\Disco\Attributes\Parameter;
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
