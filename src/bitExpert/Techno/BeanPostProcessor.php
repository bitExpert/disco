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

namespace bitExpert\Techno;

/**
 * The {@link \bitExpert\Techno\BeanPostProcessor} is an beanFactory hook that
 * allows for custom modification of new bean instances, e.g. checking for
 * marker interfaces.
 */
interface BeanPostProcessor
{
    /**
     * Apply this BeanPostProcessor to the given new bean instance after the
     * bean got created.
     *
     * @param object $bean
     * @param string $beanName
     */
    public function postProcess($bean, $beanName);
}
