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

/**
 * Unit test for {@link \bitExpert\Disco\Annotations\Parameter}.
 */
class ParameterUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     */
    public function missingNameWillThrowAnnotationException()
    {
        $parameter = new Parameter();
    }

    /**
     * @test
     */
    public function parameterNameGetsRecognizedCorrectly()
    {
        $parameter = new Parameter(['value' => ['name' => 'myParam']]);

        $this->assertSame('myParam', $parameter->getName());
    }

    /**
     * @test
     */
    public function defaultValueDefaultsToNull()
    {
        $parameter = new Parameter(['value' => ['name' => 'myParam']]);

        $this->assertNull($parameter->getDefaultValue());
    }

    /**
     * @test
     * @dataProvider defaultValueDataProvider
     */
    public function defaultValueGetsRecognizedCorrectly($defaultValue)
    {
        $parameter = new Parameter(['value' => ['name' => 'myParam', 'default' => $defaultValue]]);

        $this->assertSame($defaultValue, $parameter->getDefaultValue());
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
}
