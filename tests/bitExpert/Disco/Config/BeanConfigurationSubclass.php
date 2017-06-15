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
use bitExpert\Disco\Helper\MasterService;

/**
 * @Configuration
 */
class BeanConfigurationSubclass extends BeanConfiguration
{
    /**
     * @Bean({"singleton"=true, "lazy"=false, "scope"="session"})
     */
    public function singletonNonLazySessionBeanInSubclass(): MasterService
    {
        return new MasterService($this->singletonNonLazyRequestBean());
    }
}
