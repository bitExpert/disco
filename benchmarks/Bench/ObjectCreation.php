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

use bitExpert\Disco\Asset\MyConfiguration;

/**
 * @Revs(1000)
 * @Iterations(20)
 * @Warmup(2)
 */
class ObjectCreation
{
    /** @var BeanFactory */
    private $disco;

    public function __construct()
    {
        $this->disco = new \bitExpert\Disco\AnnotationBeanFactory(MyConfiguration::class);
        \bitExpert\Disco\BeanFactoryRegistry::register($this->disco);
    }

    public function benchCreateInstance()
    {
        $this->disco->get('mySimpleService');
    }

    public function benchCreateComplex()
    {
        $this->disco->get('J');
    }
}
