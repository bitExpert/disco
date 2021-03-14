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

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for {@link \bitExpert\Disco\Annotations\AnnotationAttributeParser}.
 */
class AnnotationAttributeParserUnitTest extends TestCase
{
    /**
     * @test
     * @dataProvider requireDataProvider
     * @param mixed $parameterValue
     * @param mixed $expectedValue
     */
    public function requireGetsRecognizedCorrectly(mixed $parameterValue, mixed $expectedValue): void
    {
        self::assertSame($expectedValue, AnnotationAttributeParser::parseBooleanValue($parameterValue));
    }

    /**
     * @return array<mixed, mixed>
     */
    public function requireDataProvider(): array
    {
        $callable = function (): void {
        };

        return [
            [true, true],
            [false, false],
            ['true', true],
            ['false', false],
            ['anything else', false],
            [1, true],
            [0, false],
            [new \stdClass(), false],
            [[], false],
            [$callable, false]
        ];
    }
}
