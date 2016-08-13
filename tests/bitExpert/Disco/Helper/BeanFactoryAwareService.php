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

namespace bitExpert\Disco\Helper;

use bitExpert\Disco\BeanFactory;
use bitExpert\Disco\BeanFactoryAware;

class BeanFactoryAwareService implements BeanFactoryAware
{
    /**
     * @var BeanFactory
     */
    protected $beanFactory;

    /**
     * @return BeanFactory
     */
    public function getBeanFactory()
    {
        return $this->beanFactory;
    }

    /**
     * @param BeanFactory $beanFactory
     */
    public function setBeanFactory(BeanFactory $beanFactory)
    {
        $this->beanFactory = $beanFactory;
    }
}
