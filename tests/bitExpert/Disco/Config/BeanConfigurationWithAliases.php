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

use bitExpert\Disco\Annotations\Alias;
use bitExpert\Disco\Annotations\Bean;
use bitExpert\Disco\Annotations\Configuration;
use bitExpert\Disco\Annotations\TypeAlias;
use bitExpert\Disco\Helper\SampleService;
use bitExpert\Disco\Helper\SampleServiceInterface;

#[Configuration]
class BeanConfigurationWithAliases
{
    #[Bean(singleton: true)]
    #[Alias(name: '\my\Custom\Namespace')]
    #[Alias(name: 'my::Custom::Namespace')]
    #[Alias(name: 'Alias_With_Underscore')]
    #[Alias(name: '123456')]
    #[TypeAlias]
    public function sampleServiceWithAliases(): SampleService
    {
        return new SampleService();
    }

    #[Bean]
    #[TypeAlias]
    public function sampleServiceWithInterfaceReturnTypeAlias(): SampleServiceInterface
    {
        return new SampleService();
    }

    #[Bean]
    #[Alias(name: 'aliasIsPublicForInternalService')]
    protected function internalServiceWithAlias(): SampleService
    {
        return new SampleService();
    }
}
