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

namespace bitExpert\Disco;

use bitExpert\Disco\Config\BeanConfiguration;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for {@link \bitExpert\Disco\BeanFactoryRegistry}.
 */
class BeanFactoryRegistryUnitTest extends TestCase
{
    /**
     * @test
     */
    public function returnsNullWhenNotInitialized()
    {
        $reflectedClass = new \ReflectionClass(BeanFactoryRegistry::class);
        $reflectedProperty = $reflectedClass->getProperty('beanFactory');
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue(null);

        $this->assertNull(BeanFactoryRegistry::getInstance());
    }
    /**
     * @test
     */
    public function returnsRegisteredInstance()
    {
        $beanFactory = new AnnotationBeanFactory(BeanConfiguration::class);
        BeanFactoryRegistry::register($beanFactory);

        $this->assertSame($beanFactory, BeanFactoryRegistry::getInstance());
    }
}
