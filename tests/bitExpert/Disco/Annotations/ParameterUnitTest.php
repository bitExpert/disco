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

use bitExpert\Disco\Attributes\Parameter;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

/**
 * Unit tests for {@link \bitExpert\Disco\Attributes\Parameter}.
 */
class ParameterUnitTest extends TestCase
{
    /**
     * @test
     */
    public function emptyNameWillThrowAnnotationException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Parameter('', 'myParam');
    }

    /**
     * @test
     */
    public function emptyKeyWillThrowAnnotationException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Parameter('name', '');
    }

    /**
     * @test
     */
    public function nameIsSet(): void
    {
        $parameter = new Parameter(name: 'paramName', key: 'key');

        self::assertSame('paramName', $parameter->getName());
    }

    /**
     * @test
     */
    public function keyIsSet(): void
    {
        $parameter = new Parameter(name: 'paramName', key: 'key');

        self::assertSame('key', $parameter->getKey());
    }

    /**
     * @test
     */
    public function defaultValueDefaultsToNull(): void
    {
        $parameter = new Parameter(name: 'paramName', key: 'myParam');

        self::assertNull($parameter->getDefaultValue());
    }

    /**
     * @test
     * @dataProvider defaultValueDataProvider
     * @param mixed $defaultValue
     */
    public function defaultValueIsParsed(mixed $defaultValue): void
    {
        $parameter = new Parameter(name: 'paramName', key: 'myParam', default: $defaultValue);

        self::assertSame($defaultValue, $parameter->getDefaultValue());
    }

    /**
     * @test
     */
    public function requireDefaultsToTrue(): void
    {
        $parameter = new Parameter(name: 'paramName', key: 'myParam');

        self::assertTrue($parameter->isRequired());
    }

    /**
     * @test
     * @dataProvider requireDataProvider
     * @param bool $requireValue
     */
    public function requireIsParsed(bool $requireValue): void
    {
        $parameter = new Parameter(name: 'paramName', key: 'myParam', required: $requireValue);

        self::assertSame($requireValue, $parameter->isRequired());
    }

    /**
     * @return array<mixed>
     */
    public function defaultValueDataProvider(): array
    {
        return [
            'defaultValue is a string' => ['myDefaultValue'],
            [0],
            [1],
            [true],
            [false],
            [null]
        ];
    }

    /**
     * @return array<int, array<int, bool>>
     */
    public function requireDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
