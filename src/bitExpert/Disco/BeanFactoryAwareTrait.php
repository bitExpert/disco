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
 * Basic implementation of {@link \bitExpert\Disco\BeanFactoryAware} interface.
 *
 * @api
 */
trait BeanFactoryAwareTrait
{
    /**
     * @var BeanFactory beanFactory instance
     */
    private $beanFactory;

    /**
     * {@inheritDoc}
     */
    public function setBeanFactory(BeanFactory $beanFactory)
    {
        $this->beanFactory = $beanFactory;
    }
}
