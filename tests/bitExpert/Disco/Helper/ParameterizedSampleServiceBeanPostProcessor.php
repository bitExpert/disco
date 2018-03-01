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

class ParameterizedSampleServiceBeanPostProcessor implements BeanPostProcessor
{
    protected $dependency;

    /**
     * Creates a new {@link \bitExpert\Disco\Helper\ParameterizedSampleServiceBeanPostProcessor}.
     *
     * @param mixed $dependency
     */
    public function __construct($dependency)
    {
        $this->dependency = $dependency;
    }

    /**
     * {@inheritDoc}
     */
    public function postProcess(object $bean, string $beanName): void
    {
        if ($bean instanceof SampleService) {
            $bean->setTest($this->dependency);
        }
    }
}
