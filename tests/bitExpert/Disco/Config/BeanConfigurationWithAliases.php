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
class BeanConfigurationWithAliases
{
    /**
     * @Bean({"alias"="\my\Custom\Namespace"})
     */
    public function sampleServiceWithBackSlashesInAlias() : SampleService
    {
        return new SampleService();
    }

    /**
     * @Bean({"alias"="my::Custom::Namespace"})
     */
    public function sampleServiceWithColonInAlias() : SampleService
    {
        return new SampleService();
    }

    /**
     * @Bean({"alias"="Alias_With_Underscore"})
     */
    public function sampleServiceWithUnderscoreInAlias() : SampleService
    {
        return new SampleService();
    }

    /**
     * @Bean({"alias"="123456"})
     */
    public function sampleServiceWithNumericAlias() : SampleService
    {
        return new SampleService();
    }


    /**
     * @Bean({"alias"="aliasIsPublicForInternalService"})
     */
    protected function internalServiceWithAlias() : SampleService
    {
        return new SampleService();
    }
}
