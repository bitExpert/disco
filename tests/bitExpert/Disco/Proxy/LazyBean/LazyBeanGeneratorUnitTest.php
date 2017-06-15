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

namespace bitExpert\Disco\Proxy\LazyBean;

use Iterator;
use PHPUnit\Framework\TestCase;
use ProxyManager\Proxy\VirtualProxyInterface;
use ReflectionClass;
use Zend\Code\Generator\ClassGenerator;

/**
 * Unit tests for {@link \bitExpert\Disco\Proxy\LazyBean\LazyBeanGenerator}.
 */
class LazyBeanGeneratorUnitTest extends TestCase
{
    /**
     * @test
     */
    public function generateWithInterfaceAsOriginalObject()
    {
        $classGenerator = $this->createMock(ClassGenerator::class);
        $classGenerator->expects(self::once())
            ->method('setImplementedInterfaces')
            ->with(array(VirtualProxyInterface::class, Iterator::class));

        $reflectionClass = new ReflectionClass(Iterator::class);
        self::assertTrue($reflectionClass->isInterface());

        $proxyGenerator = new LazyBeanGenerator();
        $proxyGenerator->generate($reflectionClass, $classGenerator);
    }
}
