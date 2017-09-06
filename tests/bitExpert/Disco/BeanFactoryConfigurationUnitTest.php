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
use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
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
     */
    public function invalidProxyTargetDirThrowsException()
    {
        self::expectException(InvalidArgumentException::class);

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
        self::assertNotContains($autoloader, $autoloaderFunctionsBeforeBeanFactoryInit);
        self::assertContains($autoloader, $autoloaderFunctionsAfterBeanFactoryInit);
    }

    /**
     * @test
     */
    public function existingProxyAutoloaderCanBeUnregistered()
    {
        $autoloader1 = $this->createMock(AutoloaderInterface::class);
        $autoloader2 = $this->createMock(AutoloaderInterface::class);

        $config = new BeanFactoryConfiguration(sys_get_temp_dir());

        // Set first proxy autoloader
        $config->setProxyAutoloader($autoloader1);
        // Set second proxy autoloader to unregister the first one
        $config->setProxyAutoloader($autoloader2);

        $proxyManagerConfig = $config->getProxyManagerConfiguration();

        self::assertSame($autoloader2, $proxyManagerConfig->getProxyAutoloader());
        self::assertContains($autoloader2, spl_autoload_functions());
        self::assertNotContains($autoloader1, spl_autoload_functions());
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

    /**
     * @test
     */
    public function injectedInvalidProxyTargetDirThrowsException()
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionCode(10);

        $config = new BeanFactoryConfiguration(sys_get_temp_dir());
        $config->setProxyTargetDir('/abc');
    }

    /**
     * @test
     */
    public function injectedNotWritableProxyTargetDirThrowsException()
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionCode(20);

        $config = new BeanFactoryConfiguration(sys_get_temp_dir());
        $path = vfsStream::setup('root', 0x111);
        $config->setProxyTargetDir($path->url());
    }
}
