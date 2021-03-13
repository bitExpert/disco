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

use Doctrine\Common\Annotations\AnnotationException;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for {@link \bitExpert\Disco\Annotations\Parameter}.
 */
class ParameterUnitTest extends TestCase
{
    /**
     * @test
     */
    public function missingNameWillThrowAnnotationException(): void
    {
        $this->expectException(AnnotationException::class);

        new Parameter();
    }

    /**
     * @test
     */
    public function parameterNameIsParsed(): void
    {
        $parameter = new Parameter(['value' => ['name' => 'myParam']]);

        self::assertSame('myParam', $parameter->getName());
    }

    /**
     * @test
     */
    public function defaultValueDefaultsToNull(): void
    {
        $parameter = new Parameter(['value' => ['name' => 'myParam']]);

        self::assertNull($parameter->getDefaultValue());
    }

    /**
     * @test
     * @dataProvider defaultValueDataProvider
     * @param mixed $defaultValue
     */
    public function defaultValueIsParsed(mixed $defaultValue): void
    {
        $parameter = new Parameter(['value' => ['name' => 'myParam', 'default' => $defaultValue]]);

        self::assertSame($defaultValue, $parameter->getDefaultValue());
    }

    /**
     * @test
     */
    public function requireDefaultsToTrue(): void
    {
        $parameter = new Parameter(['value' => ['name' => 'myParam']]);

        self::assertTrue($parameter->isRequired());
    }

    /**
     * @test
     * @dataProvider requireDataProvider
     * @param bool $requireValue
     */
    public function requireIsParsed(bool $requireValue): void
    {
        $parameter = new Parameter(['value' => ['name' => 'myParam', 'required' => $requireValue]]);

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
