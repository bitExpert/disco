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

namespace bitExpert\Disco\Proxy\Configuration;

use bitExpert\Disco\Config\BeanConfiguration;
use bitExpert\Disco\Config\InterfaceConfiguration;
use bitExpert\Disco\Config\InvalidConfiguration;
use bitExpert\Disco\Config\MissingBeanAnnotationConfiguration;
use bitExpert\Disco\Config\MissingReturnTypeConfiguration;
use bitExpert\Disco\Config\NonExistentReturnTypeConfiguration;
use PHPUnit_Framework_MockObject_MockObject;
use ProxyManager\Generator\ClassGenerator;

/**
 * Unit test for {@link \bitExpert\Disco\Proxy\Configuration\ConfigurationGenerator}.
 */
class ConfigurationGeneratorUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigurationGenerator
     */
    private $configGenerator;
    /**
     * @var ClassGenerator|PHPUnit_Framework_MockObject_MockObject
     */
    private $classGenerator;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->configGenerator = new ConfigurationGenerator();
        $this->classGenerator = $this->createMock(ClassGenerator::class);
    }

    /**
     * @test
     * @expectedException \ProxyManager\Exception\InvalidProxiedClassException
     */
    public function configClassWithoutAnAnnotationThrowsException()
    {
        $reflClass = new \ReflectionClass(InvalidConfiguration::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }

    /**
     * @test
     * @expectedException \ProxyManager\Exception\InvalidProxiedClassException
     */
    public function passingInterfaceAsConfigClassThrowsException()
    {
        $reflClass = new \ReflectionClass(InterfaceConfiguration::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }

    /**
     * @test
     * @expectedException \ProxyManager\Exception\InvalidProxiedClassException
     */
    public function missingBeanAnnotationThrowsException()
    {
        $reflClass = new \ReflectionClass(MissingBeanAnnotationConfiguration::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }

    /**
     * @test
     * @expectedException \ProxyManager\Exception\InvalidProxiedClassException
     */
    public function missingReturnTypeOfBeanDeclarationThrowsException()
    {
        $reflClass = new \ReflectionClass(MissingReturnTypeConfiguration::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }

    /**
     * @test
     * @expectedException \ProxyManager\Exception\InvalidProxiedClassException
     */
    public function nonExistentClassInReturnTypeThrowsException()
    {
        $reflClass = new \ReflectionClass(NonExistentReturnTypeConfiguration::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }

    /**
     * @test
     */
    public function parsingConfigurationWithoutAnyErrorsSucceeds()
    {
        $this->classGenerator->expects($this->any())
            ->method('addMethodFromGenerator');

        $reflClass = new \ReflectionClass(BeanConfiguration::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }
}
