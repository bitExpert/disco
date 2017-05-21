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

namespace bitExpert\Disco\Proxy\Configuration\MethodGenerator;

use bitExpert\Disco\Annotations\Bean;
use bitExpert\Disco\Annotations\Parameters;
use bitExpert\Disco\BeanException;
use bitExpert\Disco\Config\MissingReturnTypeConfiguration;
use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\BeanFactoryConfigurationProperty;
use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\BeanPostProcessorsProperty;
use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\ForceLazyInitProperty;
use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\SessionBeansProperty;
use PHPUnit\Framework\TestCase;
use Zend\Code\Reflection\MethodReflection;

/**
 * Unit tests for {@link \bitExpert\Disco\Proxy\Configuration\MethodGenerator\BeanMethod}.
 */
class BeanMethodUnitTest extends TestCase
{
    /**
     * @todo Check if this tested part of the code can even be reached.
     *
     * @test
     */
    public function generateMethodWithBodyOfUnknownBeanType()
    {
        $methodReflection = new MethodReflection(MissingReturnTypeConfiguration::class, 'nonSingletonNonLazyRequestBean');

        $methodGenerator = BeanMethod::generateMethod(
            $methodReflection,
            new Bean(),
            new Parameters(),
            'foo',
            $this->createMock(ForceLazyInitProperty::class),
            $this->createMock(SessionBeansProperty::class),
            $this->createMock(BeanPostProcessorsProperty::class),
            $this->createMock(BeanFactoryConfigurationProperty::class),
            $this->createMock(GetParameter::class),
            $this->createMock(WrapBeanAsLazy::class)
        );

        $body = '$message = sprintf(' . PHP_EOL;
        $body .= '    \'Either return type declaration missing or unknown for bean with id "' .
            'nonSingletonNonLazyRequestBean": %s\',' . PHP_EOL;
        $body .= '    $e->getMessage()' . PHP_EOL;
        $body .= ');' . PHP_EOL;
        $body .= 'throw new \\' . BeanException::class . '($message, 0, $e);' . PHP_EOL;

        self::assertSame($body, $methodGenerator->getBody());
    }
}
