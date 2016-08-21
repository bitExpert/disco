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

use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\VoidCache;
use ProxyManager\Autoloader\Autoloader;
use ProxyManager\Autoloader\AutoloaderInterface;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use ProxyManager\Inflector\ClassNameInflector;

/**
 * Unit test for {@link \bitExpert\Disco\BeanFactoryConfiguration}.
 */
class BeanFactoryConfigurationUnitTest extends \PHPUnit_Framework_TestCase
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

        self::assertSame(sys_get_temp_dir(), $config->getProxyTargetDir());
    }

    /**
     * @test
     */
    public function annotationCacheWillDefaultToProxyTargetDir()
    {
        $config = new BeanFactoryConfiguration(sys_get_temp_dir());

        /** @var FilesystemCache $annotationCache */
        $annotationCache = $config->getAnnotationCache();
        self::assertInstanceOf(FilesystemCache::class, $annotationCache);
        self::assertSame(sys_get_temp_dir(), $annotationCache->getDirectory());
    }

    /**
     * @test
     */
    public function customAnnotationCacheInstanceCanBeRetrieved()
    {
        $config = new BeanFactoryConfiguration(sys_get_temp_dir(), null, new VoidCache());

        $annotationCache = $config->getAnnotationCache();
        self::assertInstanceOf(VoidCache::class, $annotationCache);
    }

    /**
     * @test
     */
    public function customGeneratorStrategyInstanceCanBeRetrieved()
    {
        $config = new BeanFactoryConfiguration(sys_get_temp_dir(), new EvaluatingGeneratorStrategy());

        $proxyGeneratorStrategy = $config->getProxyGeneratorStrategy();
        self::assertInstanceOf(EvaluatingGeneratorStrategy::class, $proxyGeneratorStrategy);
    }

    /**
     * @test
     */
    public function configuredProxyAutoloaderInstanceCanBeRetrieved()
    {
        $autoloader = $this->createMock(AutoloaderInterface::class);
        $config = new BeanFactoryConfiguration(sys_get_temp_dir(), null, null, $autoloader);

        self::assertSame($autoloader, $config->getProxyAutoloader());
    }

    /**
     * @test
     */
    public function enablingProxyAutoloaderRegistersAdditionalAutoloader()
    {
        $autoloader = new Autoloader(new FileLocator(sys_get_temp_dir()), new ClassNameInflector('AUTOLOADER'));
        $autoloaderFunctionsBeforeBeanFactoryInit = spl_autoload_functions();

        $beanFactoryConfig = new BeanFactoryConfiguration(sys_get_temp_dir(), null, null, $autoloader);

        $autoloaderFunctionsAfterBeanFactoryInit = spl_autoload_functions();
        self::assertCount(
            count($autoloaderFunctionsBeforeBeanFactoryInit) + 1,
            $autoloaderFunctionsAfterBeanFactoryInit
        );
    }

    /**
     * @test
     */
    public function getDefaultMethodReturnsConfiguredBeanFactoryConfiguration()
    {
        $config = BeanFactoryConfiguration::getDefault(sys_get_temp_dir());

        self::assertInstanceOf(BeanFactoryConfiguration::class, $config);
        self::assertSame(sys_get_temp_dir(), $config->getProxyTargetDir());
        self::assertInstanceOf(FilesystemCache::class, $config->getAnnotationCache());
        self::assertInstanceOf(FileWriterGeneratorStrategy::class, $config->getProxyGeneratorStrategy());
        self::assertInstanceOf(Autoloader::class, $config->getProxyAutoloader());
    }
}
