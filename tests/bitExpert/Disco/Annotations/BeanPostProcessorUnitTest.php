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
 * Unit tests for {@link \bitExpert\Disco\Annotations\BeanPostProcessorUnitTest}.
 */
class BeanPostProcessorUnitTest extends TestCase
{
    /**
     * @test
     */
    public function configuredParametersGetReturned()
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
                function (Parameter $parameter) {
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
    public function throwsExceptionIfParameterTypeDoesNotMatch()
    {
        self::expectException(TypeError::class);

        $bean = new Bean([
            'value' => [
                'parameters' => [
                    new SampleService()
                ]
            ]
        ]);
    }
}
