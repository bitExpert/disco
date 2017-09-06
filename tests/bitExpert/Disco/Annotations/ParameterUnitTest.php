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
    public function missingNameWillThrowAnnotationException()
    {
        self::expectException(AnnotationException::class);

        new Parameter();
    }

    /**
     * @test
     */
    public function parameterNameIsParsed()
    {
        $parameter = new Parameter(['value' => ['name' => 'myParam']]);

        self::assertSame('myParam', $parameter->getName());
    }

    /**
     * @test
     */
    public function defaultValueDefaultsToNull()
    {
        $parameter = new Parameter(['value' => ['name' => 'myParam']]);

        self::assertNull($parameter->getDefaultValue());
    }

    /**
     * @test
     * @dataProvider defaultValueDataProvider
     */
    public function defaultValueIsParsed($defaultValue)
    {
        $parameter = new Parameter(['value' => ['name' => 'myParam', 'default' => $defaultValue]]);

        self::assertSame($defaultValue, $parameter->getDefaultValue());
    }

    /**
     * @test
     */
    public function requireDefaultsToTrue()
    {
        $parameter = new Parameter(['value' => ['name' => 'myParam']]);

        self::assertTrue($parameter->isRequired());
    }

    /**
     * @test
     * @dataProvider requireDataProvider
     */
    public function requireIsParsed($requireValue)
    {
        $parameter = new Parameter(['value' => ['name' => 'myParam', 'required' => $requireValue]]);

        self::assertSame($requireValue, $parameter->isRequired());
    }

    public function defaultValueDataProvider()
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

    public function requireDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }
}
