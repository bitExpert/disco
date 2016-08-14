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

namespace bitExpert\Disco;

/**
 * Describes an BeanFactory-aware instance.
 *
 * @api
 */
interface BeanFactoryAware
{
    /**
     * Sets a {@link \bitExpert\Disco\BeanFactory} instance on the object.
     *
     * @param BeanFactory $beanFactory
     */
    public function setBeanFactory(BeanFactory $beanFactory);
}
