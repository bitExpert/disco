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

namespace bitExpert\Techno\Config;

use bitExpert\Techno\Annotations\Alias;
use bitExpert\Techno\Annotations\Bean;
use bitExpert\Techno\Annotations\Configuration;
use bitExpert\Techno\Helper\SampleService;
use bitExpert\Techno\Helper\SampleServiceInterface;

/**
 * @Configuration
 */
class BeanConfigurationWithConflictingAliases
{
    /**
     * @Bean({
     *   "aliases"={
     *     @Alias({"type"=true})
     *   }
     * })
     * @return SampleServiceInterface
     */
    public function sampleService1(): SampleServiceInterface
    {
        return new SampleService();
    }

    /**
     * @Bean({
     *   "aliases"={
     *     @Alias({"type"=true})
     *   }
     * })
     * @return SampleServiceInterface
     */
    public function sampleService2(): SampleServiceInterface
    {
        return new SampleService();
    }
}
