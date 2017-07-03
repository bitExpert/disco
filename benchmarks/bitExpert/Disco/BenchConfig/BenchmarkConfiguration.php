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

namespace bitExpert\Techno\BenchConfig;

use bitExpert\Techno\Annotations\Bean;
use bitExpert\Techno\Annotations\Configuration;
use bitExpert\Techno\BenchHelper\A;
use bitExpert\Techno\BenchHelper\B;
use bitExpert\Techno\BenchHelper\C;
use bitExpert\Techno\BenchHelper\D;
use bitExpert\Techno\BenchHelper\E;
use bitExpert\Techno\BenchHelper\F;
use bitExpert\Techno\BenchHelper\G;
use bitExpert\Techno\BenchHelper\H;
use bitExpert\Techno\BenchHelper\I;
use bitExpert\Techno\BenchHelper\J;

/**
 * @Configuration
 */
class BenchmarkConfiguration
{
    /**
     * @Bean({"alias"="simpleService"})
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
     * @Bean({"alias"="complexService"})
     */
    public function J(): J
    {
        return new J($this->I());
    }
}
