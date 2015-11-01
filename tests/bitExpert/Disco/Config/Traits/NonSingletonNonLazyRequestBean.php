<?php

/*
 * This file is part of the Disco package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bitExpert\Disco\Config\Traits;

use bitExpert\Disco\Annotations\Bean;

trait NonSingletonNonLazyRequestBean
{
    /**
     * @Bean({"singleton"=false, "lazy"=false, "scope"="request"})
     * @return \bitExpert\Disco\Helper\SampleService
     */
    public function nonSingletonNonLazyRequestBeanInTrait()
    {
        return new \bitExpert\Disco\Helper\SampleService();
    }
}
