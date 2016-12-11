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

namespace bitExpert\Disco\Bench;

use bitExpert\Disco\AnnotationBeanFactory;
use bitExpert\Disco\BeanFactoryConfiguration;
use bitExpert\Disco\BeanFactoryRegistry;
use bitExpert\Disco\BenchConfig\BenchmarkConfiguration;
use ProxyManager\Autoloader\Autoloader;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\Inflector\ClassNameInflector;

/**
 * @Revs({1, 100, 1000})
 * @Iterations(20)
 * @Warmup(2)
 */
class BenchmarkDisco
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

    public function createDiscoInstance(bool $useAutoloader = false) : AnnotationBeanFactory
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

        $disco = new AnnotationBeanFactory(BenchmarkConfiguration::class, [], $config);
        BeanFactoryRegistry::register($disco);
        return $disco;
    }

    /**
     * @ParamProviders({"provideUseAutoloader", "provideIds"})
     */
    public function benchDisco($params)
    {
        /** @var AnnotationBeanFactory $disco */
        $disco = $this->createDiscoInstance((bool) $params['useAutoloader']);
        $beanId = $params['beanId'];

        $instance = $disco->get($beanId);
    }
}
