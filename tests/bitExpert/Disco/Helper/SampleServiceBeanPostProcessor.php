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

use bitExpert\Disco\BeanPostProcessor;

class SampleServiceBeanPostProcessor implements BeanPostProcessor
{
    /**
     * {@inheritDoc}
     */
    public function postProcess(object $bean, string $beanName): void
    {
        if ($bean instanceof SampleService) {
            $bean->setTest('postProcessed');
        }
    }
}
