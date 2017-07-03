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
class BeanConfigurationWithAliases
{
    /**
     * @Bean({
     *   "singelton"=true,
     *   "aliases"={
     *      @Alias({"name" = "\my\Custom\Namespace"}),
     *      @Alias({"name" = "my::Custom::Namespace"}),
     *      @Alias({"name" = "Alias_With_Underscore"}),
     *      @Alias({"name" = "123456"}),
     *      @Alias({"type" = true})
     *   }
     * })
     */
    public function sampleServiceWithAliases(): SampleService
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
    public function sampleServiceWithInterfaceReturnTypeAlias(): SampleServiceInterface
    {
        return new SampleService();
    }

    /**
     * @Bean({
     *   "aliases"={
     *      @Alias({"name"="aliasIsPublicForInternalService"})
     *   }
     * })
     */
    protected function internalServiceWithAlias(): SampleService
    {
        return new SampleService();
    }
}
