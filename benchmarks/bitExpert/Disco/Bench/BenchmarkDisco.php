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

namespace bitExpert\Techno\Bench;

use bitExpert\Techno\AnnotationBeanFactory;
use bitExpert\Techno\BeanFactoryConfiguration;
use bitExpert\Techno\BeanFactoryRegistry;
use bitExpert\Techno\BenchConfig\BenchmarkConfiguration;
use ProxyManager\Autoloader\Autoloader;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\Inflector\ClassNameInflector;

/**
 * @Revs({1, 100, 1000})
 * @Iterations(20)
 * @Warmup(2)
 */
class BenchmarkTechno
{
    public function provideUseAutoloader() : array
    {
        return [
            ['useAutoloader' => true],
            ['useAutoloader' => false],
        ];
    }

    public function provideIds() : array
    {
        return [
            ['beanId' => 'A'],
            ['beanId' => 'J'],
            ['beanId' => 'simpleService'],
            ['beanId' => 'complexService'],
        ];
    }

    public function createTechnoInstance(bool $useAutoloader = false) : AnnotationBeanFactory
    {
        $config = new BeanFactoryConfiguration(sys_get_temp_dir());
        if($useAutoloader) {
            $config->setProxyAutoloader(
                new Autoloader(
                    new FileLocator(sys_get_temp_dir()),
                    new ClassNameInflector('Bench')
                )
            );
        }

        $techno = new AnnotationBeanFactory(BenchmarkConfiguration::class, [], $config);
        BeanFactoryRegistry::register($techno);
        return $techno;
    }

    /**
     * @ParamProviders({"provideUseAutoloader", "provideIds"})
     */
    public function benchTechno($params)
    {
        /** @var AnnotationBeanFactory $techno */
        $techno = $this->createTechnoInstance((bool) $params['useAutoloader']);
        $beanId = $params['beanId'];

        $instance = $techno->get($beanId);
    }
}
