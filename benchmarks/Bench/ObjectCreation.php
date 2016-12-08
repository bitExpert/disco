<?php
/*
 * This file is part of the Disco package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bitExpert\Disco\Bench;

use bitExpert\Disco\Asset\BenchmarkConfiguration;

/**
 * @Revs({1, 100, 1000})
 * @Iterations(20)
 * @Warmup(2)
 */
class ObjectCreation extends BaseObjectCreation
{
    public function __construct()
    {
        $this->disco = new \bitExpert\Disco\AnnotationBeanFactory(BenchmarkConfiguration::class);
        \bitExpert\Disco\BeanFactoryRegistry::register($this->disco);
    }
}
