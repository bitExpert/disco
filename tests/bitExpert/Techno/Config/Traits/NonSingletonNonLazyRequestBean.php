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

namespace bitExpert\Techno\Config\Traits;

use bitExpert\Techno\Annotations\Bean;
use bitExpert\Techno\Helper\SampleService;

trait NonSingletonNonLazyRequestBean
{
    /**
     * @Bean({"singleton"=false, "lazy"=false, "scope"="request"})
     */
    public function nonSingletonNonLazyRequestBeanInTrait(): SampleService
    {
        return new SampleService();
    }
}
