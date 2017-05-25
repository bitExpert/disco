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

use AspectMock\Test as AspectMock;
use bitExpert\Disco\Store\SerializableBeanStore;
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
    public function existingProxyAutoloaderCanBeUnregistered()
    {
        $unregisterSpy = AspectMock::func(__NAMESPACE__, 'spl_autoload_unregister', function ($autoloader) {
            return \spl_autoload_unregister($autoloader);
        });

        $autoloader1 = $this->createMock(AutoloaderInterface::class);
        $autoloader2 = $this->createMock(AutoloaderInterface::class);

        $config = new BeanFactoryConfiguration(sys_get_temp_dir());
        // Set first proxy autoloader
        $config->setProxyAutoloader($autoloader1);
        // Set second proxy autoloader to unregister the first one
        $config->setProxyAutoloader($autoloader2);
        $unregisterSpy->verifyInvokedOnce([$autoloader1]);

        $proxyManagerConfig = $config->getProxyManagerConfiguration();

        self::assertSame($autoloader2, $proxyManagerConfig->getProxyAutoloader());

        AspectMock::clean();
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
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 10
     */
    public function injectedInvalidProxyTargetDirThrowsException()
    {
        $config = new BeanFactoryConfiguration(sys_get_temp_dir());
        $config->setProxyTargetDir('/abc');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 20
     */
    public function injectedNotWritableProxyTargetDirThrowsException()
    {
        $config = new BeanFactoryConfiguration(sys_get_temp_dir());
        $path = vfsStream::setup('root', 0x111);
        $config->setProxyTargetDir($path->url());
    }
}
