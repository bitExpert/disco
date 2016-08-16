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
use bitExpert\Disco\Helper\InitializedService;
use bitExpert\Disco\Helper\MasterService;
use bitExpert\Disco\Helper\SampleService;

/**
 * @Configuration
 */
class BeanConfigurationWithNormalizedIds
{
    /**
     * @Bean
     */
    public function myCustomNamespace() : SampleService
    {
        return new SampleService();
    }

    /**
     * @Bean
     */
    // @codingStandardsIgnoreStart
    public function Bean_With_Underscores() : SampleService
    {
        return new SampleService();
    }
    // @codingStandardsIgnoreEnd

    /**
     * @Bean
     */
    // @codingStandardsIgnoreStart
    public function _1Bean() : SampleService
    {
        return new SampleService();
    }
    // @codingStandardsIgnoreEnd
}
