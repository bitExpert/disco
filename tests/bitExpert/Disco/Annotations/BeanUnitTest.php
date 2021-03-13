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

namespace bitExpert\Disco\Annotations;

use bitExpert\Disco\Helper\SampleService;
use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * Unit tests for {@link \bitExpert\Disco\Annotations\Bean}.
 */
class BeanUnitTest extends TestCase
{
    /**
     * @test
     */
    public function emptyAttributesArraySetsDefaultValues(): void
    {
        $bean = new Bean();

        self::assertTrue($bean->isRequest());
        self::assertFalse($bean->isSession());
        self::assertTrue($bean->isSingleton());
        self::assertFalse($bean->isLazy());
        self::assertEmpty($bean->getAliases());
        self::assertEmpty($bean->getParameters());
    }

    /**
     * @test
     */
    public function markingBeanWithSessionScope(): void
    {
        $bean = new Bean(['value' => ['scope' => 'session']]);

        self::assertTrue($bean->isSession());
        self::assertFalse($bean->isRequest());
    }

    /**
     * @test
     */
    public function markingBeanWithRequestScope(): void
    {
        $bean = new Bean(['value' => ['scope' => 'request']]);

        self::assertTrue($bean->isRequest());
        self::assertFalse($bean->isSession());
    }

    /**
     * @test
     */
    public function markingBeanAsSingleton(): void
    {
        $bean = new Bean(['value' => ['singleton' => true]]);

        self::assertTrue($bean->isSingleton());
    }

    /**
     * @test
     */
    public function markingBeanAsSingletonWithString(): void
    {
        $bean = new Bean(['value' => ['singleton' => 'true']]);

        self::assertTrue($bean->isSingleton());
    }

    /**
     * @test
     */
    public function markingBeanAsSingletonWithInt(): void
    {
        $bean = new Bean(['value' => ['singleton' => 1]]);

        self::assertTrue($bean->isSingleton());
    }

    /**
     * @test
     */
    public function markingBeanAsNonSingleton(): void
    {
        $bean = new Bean(['value' => ['singleton' => false]]);

        self::assertFalse($bean->isSingleton());
    }

    /**
     * @test
     */
    public function markingBeanAsNonSingletonWithString(): void
    {
        $bean = new Bean(['value' => ['singleton' => 'false']]);

        self::assertFalse($bean->isSingleton());
    }

    /**
     * @test
     */
    public function markingBeanAsNonSingletonWithInt(): void
    {
        $bean = new Bean(['value' => ['singleton' => 0]]);

        self::assertFalse($bean->isSingleton());
    }

    /**
     * @test
     */
    public function markingBeanAsLazy(): void
    {
        $bean = new Bean(['value' => ['lazy' => true]]);

        self::assertTrue($bean->isLazy());
    }

    /**
     * @test
     */
    public function markingBeanAsLazyWithString(): void
    {
        $bean = new Bean(['value' => ['lazy' => 'true']]);

        self::assertTrue($bean->isLazy());
    }

    /**
     * @test
     */
    public function markingBeanAsLazyWithInt(): void
    {
        $bean = new Bean(['value' => ['lazy' => 1]]);

        self::assertTrue($bean->isLazy());
    }

    /**
     * @test
     */
    public function markingBeanAsNonLazy(): void
    {
        $bean = new Bean(['value' => ['lazy' => false]]);

        self::assertFalse($bean->isLazy());
    }

    /**
     * @test
     */
    public function markingBeanAsNonLazyWithString(): void
    {
        $bean = new Bean(['value' => ['lazy' => 'false']]);

        self::assertFalse($bean->isLazy());
    }

    /**
     * @test
     */
    public function markingBeanAsNonLazyWithInt(): void
    {
        $bean = new Bean(['value' => ['lazy' => 0]]);

        self::assertFalse($bean->isLazy());
    }

    /**
     * @test
     */
    public function configuredAliasesGetReturned(): void
    {
        $bean = new Bean([
            'value' => [
                'aliases' => [
                    new Alias(['value' => ['name' => 'someAlias']]),
                    new Alias(['value' => ['name' => 'yetAnotherAlias']])
                ]
            ]
        ]);

        self::assertEquals(
            array_map(
                function (Alias $alias): ?string {
                    return $alias->getName();
                },
                $bean->getAliases()
            ),
            ['someAlias', 'yetAnotherAlias']
        );
    }

    /**
     * @test
     */
    public function throwsExceptionIfAliasTypeDoesNotMatch(): void
    {
        $this->expectException(TypeError::class);

        $bean = new Bean([
            'value' => [
                'aliases' => [
                    new SampleService()
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function configuredParametersGetReturned(): void
    {
        $bean = new Bean([
            'value' => [
                'parameters' => [
                    new Parameter(['value' => ['name' => 'parameterName']]),
                    new Parameter(['value' => ['name' => 'yetAnotherParameter']])
                ]
            ]
        ]);

        self::assertEquals(
            array_map(
                function (Parameter $parameter): string {
                    return $parameter->getName();
                },
                $bean->getParameters()
            ),
            ['parameterName', 'yetAnotherParameter']
        );
    }

    /**
     * @test
     */
    public function throwsExceptionIfParameterTypeDoesNotMatch(): void
    {
        $this->expectException(TypeError::class);

        $bean = new Bean([
            'value' => [
                'parameters' => [
                    new SampleService()
                ]
            ]
        ]);
    }
}
