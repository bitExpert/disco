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
class ObjectCreationOptimizedConfig extends BaseObjectCreation
{
    public function __construct()
    {
        $config = new \bitExpert\Disco\BeanFactoryConfiguration('/tmp/');
        $config->setProxyAutoloader(
            new \ProxyManager\Autoloader\Autoloader(
                new \ProxyManager\FileLocator\FileLocator('/tmp/'),
                new \ProxyManager\Inflector\ClassNameInflector('Disco')
            )
        );
        $this->disco = new \bitExpert\Disco\AnnotationBeanFactory(BenchmarkConfiguration::class, [], $config);
        \bitExpert\Disco\BeanFactoryRegistry::register($this->disco);
    }
}
