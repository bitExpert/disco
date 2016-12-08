<?php
/*
 * This file is part of the Disco package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bitExpert\Disco\Asset;

use bitExpert\Disco\Annotations\Configuration;
use bitExpert\Disco\Annotations\Bean;

/**
 * @Configuration
 */
class BenchmarkConfiguration
{
    /**
     * @Bean({"alias"="mySimpleService"})
     */
    public function A(): A
    {
        return new A();
    }

    /**
     * @Bean
     */
    public function B(): B
    {
        return new B($this->A());
    }

    /**
     * @Bean
     */
    public function C(): C
    {
        return new C($this->B());
    }

    /**
     * @Bean
     */
    public function D(): D
    {
        return new D($this->C());
    }

    /**
     * @Bean
     */
    public function E(): E
    {
        return new E($this->D());
    }

    /**
     * @Bean
     */
    public function F(): F
    {
        return new F($this->E());
    }

    /**
     * @Bean
     */
    public function G(): G
    {
        return new G($this->F());
    }

    /**
     * @Bean
     */
    public function H(): H
    {
        return new H($this->G());
    }

    /**
     * @Bean
     */
    public function I(): I
    {
        return new I($this->H());
    }

    /**
     * @Bean
     */
    public function J(): J
    {
        return new J($this->I());
    }
}
