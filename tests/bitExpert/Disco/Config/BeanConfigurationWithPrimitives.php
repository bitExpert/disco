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
use bitExpert\Disco\Annotations\Parameters;
use bitExpert\Disco\Helper\SampleService;

/**
 * @Configuration
 */
class BeanConfigurationWithPrimitives
{
    /**
     * @Bean
     */
    public function arrayPrimitive(): array
    {
        return [];
    }

    /**
     * @Bean
     */
    public function callablePrimitive(): callable
    {
        return function () {
        };
    }

    /**
     * @Bean
     */
    public function boolPrimitive(): bool
    {
        return true;
    }

    /**
     * @Bean
     */
    public function floatPrimitive(): float
    {
        return 1.23;
    }

    /**
     * @Bean
     */
    public function intPrimitive(): int
    {
        return 5;
    }

    /**
     * @Bean
     */
    public function serviceWithStringInjected(): SampleService
    {
        $service = new SampleService();
        $service->setTest($this->stringPrimitive());
        return $service;
    }

    /**
     * @Bean
     */
    public function stringPrimitive(): string
    {
        return 'Disco';
    }
}
