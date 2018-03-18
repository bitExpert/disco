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
use bitExpert\Disco\Config\BeanConfigurationWithConflictingAliases;
use bitExpert\Disco\Config\BeanConfigurationWithConflictingAliasesInParentClass;
use bitExpert\Disco\Config\ExtendedBeanConfigurationOverwritingParentAlias;
use bitExpert\Disco\Config\BeanConfigurationWithNativeTypeAlias;
use bitExpert\Disco\Config\InterfaceConfiguration;
use bitExpert\Disco\Config\InvalidConfiguration;
use bitExpert\Disco\Config\MissingBeanAnnotationConfiguration;
use bitExpert\Disco\Config\MissingReturnTypeConfiguration;
use bitExpert\Disco\Config\NonExistentReturnTypeConfiguration;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use ProxyManager\Exception\InvalidProxiedClassException;
use Zend\Code\Generator\ClassGenerator;

/**
 * Unit tests for {@link \bitExpert\Disco\Proxy\Configuration\ConfigurationGenerator}.
 */
class ConfigurationGeneratorUnitTest extends TestCase
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
     */
    public function configClassWithoutAnAnnotationThrowsException()
    {
        self::expectException(InvalidProxiedClassException::class);

        $reflClass = new \ReflectionClass(InvalidConfiguration::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }

    /**
     * @test
     */
    public function passingInterfaceAsConfigClassThrowsException()
    {
        self::expectException(InvalidProxiedClassException::class);

        $reflClass = new \ReflectionClass(InterfaceConfiguration::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }

    /**
     * @test
     */
    public function missingBeanAnnotationThrowsException()
    {
        self::expectException(InvalidProxiedClassException::class);

        $reflClass = new \ReflectionClass(MissingBeanAnnotationConfiguration::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }

    /**
     * @test
     */
    public function missingReturnTypeOfBeanDeclarationThrowsException()
    {
        self::expectException(InvalidProxiedClassException::class);

        $reflClass = new \ReflectionClass(MissingReturnTypeConfiguration::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }

    /**
     * @test
     */
    public function nonExistentClassInReturnTypeThrowsException()
    {
        self::expectException(InvalidProxiedClassException::class);

        $reflClass = new \ReflectionClass(NonExistentReturnTypeConfiguration::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }

    /**
     * @test
     */
    public function sameAliasUsedForMultipleBeansThrowsException()
    {
        self::expectException(InvalidProxiedClassException::class);

        $reflClass = new \ReflectionClass(BeanConfigurationWithConflictingAliases::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }

    /**
     * @test
     */
    public function unknownAnnotationThrowsException()
    {
        self::expectException(InvalidProxiedClassException::class);
        self::expectExceptionMessageRegExp('/^\[Semantical Error\] The annotation "@foo"/');

        /**
         * @foo
         */
        $configObject = new class
        {
            public function foo(): string
            {
                return 'foo';
            }
        };
        $reflClass = new \ReflectionObject($configObject);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }

    /**
     * @test
     */
    public function parsingConfigurationWithoutAnyErrorsSucceeds()
    {
        $this->classGenerator->expects(self::atLeastOnce())
            ->method('addMethodFromGenerator');

        $reflClass = new \ReflectionClass(BeanConfiguration::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }

    /**
     * @test
     */
    public function subclassedConfigurationIsAllowedToOverrwriteParentAlias()
    {
        $this->classGenerator->expects(self::atLeastOnce())
            ->method('addMethodFromGenerator');

        $reflClass = new \ReflectionClass(ExtendedBeanConfigurationOverwritingParentAlias::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }

    /**
     * @test
     */
    public function parsingConfigurationWithConflictingAliasesInParentConfigurationFails()
    {
        self::expectException(InvalidProxiedClassException::class);

        $reflClass = new \ReflectionClass(BeanConfigurationWithConflictingAliasesInParentClass::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }
}
