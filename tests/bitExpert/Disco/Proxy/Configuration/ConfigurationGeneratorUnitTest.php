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
use bitExpert\Disco\Config\InterfaceConfiguration;
use bitExpert\Disco\Config\InvalidConfiguration;
use bitExpert\Disco\Config\MissingBeanAnnotationConfiguration;
use bitExpert\Disco\Config\MissingReturnTypeConfiguration;
use bitExpert\Disco\Config\NonExistentReturnTypeConfiguration;
use PHPUnit\Framework\TestCase;
use ProxyManager\Exception\InvalidProxiedClassException;
use Laminas\Code\Generator\ClassGenerator;

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
     * @var ClassGenerator&\PHPUnit\Framework\MockObject\MockObject
     */
    private $classGenerator;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->configGenerator = new ConfigurationGenerator();
        /** @var ClassGenerator&\PHPUnit\Framework\MockObject\MockObject $mock */
        $mock = $this->createMock(ClassGenerator::class);
        $this->classGenerator = $mock;
    }

    /**
     * @test
     */
    public function configClassWithoutAnAnnotationThrowsException(): void
    {
        $this->expectException(InvalidProxiedClassException::class);

        $reflClass = new \ReflectionClass(InvalidConfiguration::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }

    /**
     * @test
     */
    public function passingInterfaceAsConfigClassThrowsException(): void
    {
        $this->expectException(InvalidProxiedClassException::class);

        $reflClass = new \ReflectionClass(InterfaceConfiguration::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }

    /**
     * @test
     */
    public function missingBeanAnnotationThrowsException(): void
    {
        $this->expectException(InvalidProxiedClassException::class);

        $reflClass = new \ReflectionClass(MissingBeanAnnotationConfiguration::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }

    /**
     * @test
     */
    public function missingReturnTypeOfBeanDeclarationThrowsException(): void
    {
        $this->expectException(InvalidProxiedClassException::class);

        $reflClass = new \ReflectionClass(MissingReturnTypeConfiguration::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }

    /**
     * @test
     */
    public function nonExistentClassInReturnTypeThrowsException(): void
    {
        $this->expectException(InvalidProxiedClassException::class);

        $reflClass = new \ReflectionClass(NonExistentReturnTypeConfiguration::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }

    /**
     * @test
     */
    public function sameAliasUsedForMultipleBeansThrowsException(): void
    {
        $this->expectException(InvalidProxiedClassException::class);

        $reflClass = new \ReflectionClass(BeanConfigurationWithConflictingAliases::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }

    /**
     * @test
     */
    public function missingConfigurationAttributeThrowsException(): void
    {
        $this->expectException(InvalidProxiedClassException::class);
        $this->expectExceptionMessageMatches('/#\[Configuration\] attribute missing!/');

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
    public function parsingConfigurationWithoutAnyErrorsSucceeds(): void
    {
        $this->classGenerator->expects(self::atLeastOnce())
            ->method('addMethodFromGenerator');

        $reflClass = new \ReflectionClass(BeanConfiguration::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }

    /**
     * @test
     */
    public function subclassedConfigurationIsAllowedToOverwriteParentAlias(): void
    {
        $this->classGenerator->expects(self::atLeastOnce())
            ->method('addMethodFromGenerator');

        $reflClass = new \ReflectionClass(ExtendedBeanConfigurationOverwritingParentAlias::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }

    /**
     * @test
     */
    public function parsingConfigurationWithConflictingAliasesInParentConfigurationFails(): void
    {
        $this->expectException(InvalidProxiedClassException::class);

        $reflClass = new \ReflectionClass(BeanConfigurationWithConflictingAliasesInParentClass::class);
        $this->configGenerator->generate($reflClass, $this->classGenerator);
    }
}
