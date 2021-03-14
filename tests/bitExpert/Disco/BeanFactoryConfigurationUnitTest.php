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
    public function invalidProxyTargetDirThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new BeanFactoryConfiguration('/abc');
    }

    /**
     * @test
     */
    public function configuredProxyTargetDirCanBeRetrieved(): void
    {
        $config = new BeanFactoryConfiguration(sys_get_temp_dir());
        $proxyManagerConfig = $config->getProxyManagerConfiguration();

        self::assertSame(sys_get_temp_dir(), $proxyManagerConfig->getProxiesTargetDir());
    }

    /**
     * @test
     */
    public function configuredGeneratorStrategyInstanceCanBeRetrieved(): void
    {
        $config = new BeanFactoryConfiguration(sys_get_temp_dir());
        $config->setProxyWriterGenerator(new EvaluatingGeneratorStrategy());

        $proxyManagerConfig = $config->getProxyManagerConfiguration();

        self::assertInstanceOf(EvaluatingGeneratorStrategy::class, $proxyManagerConfig->getGeneratorStrategy());
    }

    /**
     * @test
     */
    public function configuredProxyAutoloaderInstanceCanBeRetrieved(): void
    {
        /** @var AutoloaderInterface $autoloader */
        $autoloader = $this->createMock(AutoloaderInterface::class);

        $config = new BeanFactoryConfiguration(sys_get_temp_dir());
        $config->setProxyAutoloader($autoloader);
        $proxyManagerConfig = $config->getProxyManagerConfiguration();

        self::assertSame($autoloader, $proxyManagerConfig->getProxyAutoloader());
    }

    /**
     * @test
     */
    public function enablingProxyAutoloaderRegistersAdditionalAutoloader(): void
    {
        $autoloader = new Autoloader(new FileLocator(sys_get_temp_dir()), new ClassNameInflector('AUTOLOADER'));

        /** @var array<callable> $autoloaderFunctionsBeforeBeanFactoryInit */
        $autoloaderFunctionsBeforeBeanFactoryInit = spl_autoload_functions();
        $beanFactoryConfig = new BeanFactoryConfiguration(sys_get_temp_dir());
        $beanFactoryConfig->setProxyAutoloader($autoloader);
        /** @var array<callable> $autoloaderFunctionsAfterBeanFactoryInit */
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
    public function existingProxyAutoloaderCanBeUnregistered(): void
    {
        /** @var AutoloaderInterface $autoloader1 */
        $autoloader1 = $this->createMock(AutoloaderInterface::class);
        /** @var AutoloaderInterface $autoloader2 */
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
    public function configuredBeanStoreInstanceCanBererieved(): void
    {
        $beanStore = new SerializableBeanStore();

        $config = new BeanFactoryConfiguration(sys_get_temp_dir());
        $config->setSessionBeanStore($beanStore);

        self::assertSame($beanStore, $config->getSessionBeanStore());
    }

    /**
     * @test
     */
    public function injectedInvalidProxyTargetDirThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(10);

        $config = new BeanFactoryConfiguration(sys_get_temp_dir());
        $config->setProxyTargetDir('/abc');
    }

    /**
     * @test
     */
    public function injectedNotWritableProxyTargetDirThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(20);

        $config = new BeanFactoryConfiguration(sys_get_temp_dir());
        $path = vfsStream::setup('root', 0x111);
        $config->setProxyTargetDir($path->url());
    }
}
