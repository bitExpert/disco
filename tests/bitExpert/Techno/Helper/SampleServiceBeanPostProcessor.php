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

namespace bitExpert\Techno\Helper;

use bitExpert\Techno\BeanPostProcessor;

class SampleServiceBeanPostProcessor implements BeanPostProcessor
{
    /**
     * {@inheritDoc}
     */
    public function postProcess($bean, $beanName)
    {
        if ($bean instanceof SampleService) {
            $bean->setTest('postProcessed');
        }
    }
}
