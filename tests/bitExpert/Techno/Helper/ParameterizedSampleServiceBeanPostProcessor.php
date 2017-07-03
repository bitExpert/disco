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

class ParameterizedSampleServiceBeanPostProcessor implements BeanPostProcessor
{
    protected $dependency;

    /**
     * Creates a new {@link \bitExpert\Techno\Helper\ParameterizedSampleServiceBeanPostProcessor}.
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
    public function postProcess($bean, $beanName)
    {
        if ($bean instanceof SampleService) {
            $bean->setTest($this->dependency);
        }
    }
}
