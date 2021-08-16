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

use bitExpert\Disco\Attributes\Bean;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

/**
 * Unit tests for {@link \bitExpert\Disco\Attributes\Bean}.
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
    }

    /**
     * @test
     */
    public function markingBeanWithSessionScope(): void
    {
        $bean = new Bean(scope: Bean::SCOPE_SESSION);

        self::assertTrue($bean->isSession());
        self::assertFalse($bean->isRequest());
    }

    /**
     * @test
     */
    public function markingBeanWithRequestScope(): void
    {
        $bean = new Bean(scope: Bean::SCOPE_REQUEST);

        self::assertTrue($bean->isRequest());
        self::assertFalse($bean->isSession());
    }

    /**
     * @test
     */
    public function markingBeanAsSingleton(): void
    {
        $bean = new Bean(singleton: true);

        self::assertTrue($bean->isSingleton());
    }

    /**
     * @test
     */
    public function markingBeanAsSingletonWithString(): void
    {
        $bean = new Bean(singleton: true);

        self::assertTrue($bean->isSingleton());
    }

    /**
     * @test
     */
    public function markingBeanAsNonSingleton(): void
    {
        $bean = new Bean(singleton: false);

        self::assertFalse($bean->isSingleton());
    }

    /**
     * @test
     */
    public function markingBeanAsLazy(): void
    {
        $bean = new Bean(lazy: true);

        self::assertTrue($bean->isLazy());
    }

    /**
     * @test
     */
    public function markingBeanAsNonLazy(): void
    {
        $bean = new Bean(lazy: false);

        self::assertFalse($bean->isLazy());
    }

    /**
     * @test
     */
    public function throwsExceptionIfScopeIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Bean(scope: 3);
    }
}
