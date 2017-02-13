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

namespace bitExpert\Disco;

use bitExpert\Disco\Store\SerializableBeanStore;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\VoidCache;
use PHPUnit\Framework\TestCase;
use ProxyManager\Autoloader\Autoloader;
use ProxyManager\Autoloader\AutoloaderInterface;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\Inflector\ClassNameInflector;

/**
 * Unit tests for {@link \bitExpert\Disco\BeanFactoryConfiguration}.
 */
class BeanFactoryConfigurationUnitTest extends TestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function invalidProxyTargetDirThrowsException()
    {
        new BeanFactoryConfiguration('/abc');
    }

    /**
     * @test
     */
    public function configuredProxyTargetDirCanBeRetrieved()
    {
        $config = new BeanFactoryConfiguration(sys_get_temp_dir());
        $proxyManagerConfig = $config->getProxyManagerConfiguration();

        self::assertSame(sys_get_temp_dir(), $proxyManagerConfig->getProxiesTargetDir());
    }

    /**
     * @test
     */
    public function configuredGeneratorStrategyInstanceCanBeRetrieved()
    {
        $config = new BeanFactoryConfiguration(sys_get_temp_dir());
        $config->setProxyWriterGenerator(new EvaluatingGeneratorStrategy());

        $proxyManagerConfig = $config->getProxyManagerConfiguration();

        self::assertInstanceOf(EvaluatingGeneratorStrategy::class, $proxyManagerConfig->getGeneratorStrategy());
    }

    /**
     * @test
     */
    public function configuredProxyAutoloaderInstanceCanBeRetrieved()
    {
        $autoloader = $this->createMock(AutoloaderInterface::class);

        $config = new BeanFactoryConfiguration(sys_get_temp_dir());
        $config->setProxyAutoloader($autoloader);
        $proxyManagerConfig = $config->getProxyManagerConfiguration();

        self::assertSame($autoloader, $proxyManagerConfig->getProxyAutoloader());
    }

    /**
     * @test
     */
    public function enablingProxyAutoloaderRegistersAdditionalAutoloader()
    {
        $autoloader = new Autoloader(new FileLocator(sys_get_temp_dir()), new ClassNameInflector('AUTOLOADER'));

        $autoloaderFunctionsBeforeBeanFactoryInit = spl_autoload_functions();
        $beanFactoryConfig = new BeanFactoryConfiguration(sys_get_temp_dir());
        $beanFactoryConfig->setProxyAutoloader($autoloader);
        $autoloaderFunctionsAfterBeanFactoryInit = spl_autoload_functions();

        self::assertCount(
            count($autoloaderFunctionsBeforeBeanFactoryInit) + 1,
            $autoloaderFunctionsAfterBeanFactoryInit
        );
    }

    /**
     * @test
     */
    public function configuredBeanStoreInstanceCanBererieved()
    {
        $beanStore = new SerializableBeanStore();

        $config = new BeanFactoryConfiguration(sys_get_temp_dir());
        $config->setSessionBeanStore($beanStore);

        self::assertSame($beanStore, $config->getSessionBeanStore());
    }
}
