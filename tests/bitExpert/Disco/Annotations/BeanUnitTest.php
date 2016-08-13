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
 * Unit test for {@link \bitExpert\Disco\Annotations\Bean}.
 */
class BeanUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function emptyAttributesArraySetsDefaultValues()
    {
        $bean = new Bean();

        $this->assertTrue($bean->isRequest());
        $this->assertFalse($bean->isSession());
        $this->assertTrue($bean->isSingleton());
        $this->assertFalse($bean->isLazy());
    }

    /**
     * @test
     */
    public function markingBeanWithSessionScope()
    {
        $bean = new Bean(['value' => ['scope' => 'session']]);

        $this->assertTrue($bean->isSession());
        $this->assertFalse($bean->isRequest());
    }

    /**
     * @test
     */
    public function markingBeanWithRequestScope()
    {
        $bean = new Bean(['value' => ['scope' => 'request']]);

        $this->assertTrue($bean->isRequest());
        $this->assertFalse($bean->isSession());
    }

    /**
     * @test
     */
    public function markingBeanAsSingleton()
    {
        $bean = new Bean(['value' => ['singleton' => true]]);

        $this->assertTrue($bean->isSingleton());
    }

    /**
     * @test
     */
    public function markingBeanAsSingletonWithString()
    {
        $bean = new Bean(['value' => ['singleton' => 'true']]);

        $this->assertTrue($bean->isSingleton());
    }

    /**
     * @test
     */
    public function markingBeanAsSingletonWithInt()
    {
        $bean = new Bean(['value' => ['singleton' => 1]]);

        $this->assertTrue($bean->isSingleton());
    }

    /**
     * @test
     */
    public function markingBeanAsNonSingleton()
    {
        $bean = new Bean(['value' => ['singleton' => false]]);

        $this->assertFalse($bean->isSingleton());
    }

    /**
     * @test
     */
    public function markingBeanAsNonSingletonWithString()
    {
        $bean = new Bean(['value' => ['singleton' => 'false']]);

        $this->assertFalse($bean->isSingleton());
    }

    /**
     * @test
     */
    public function markingBeanAsNonSingletonWithInt()
    {
        $bean = new Bean(['value' => ['singleton' => 0]]);

        $this->assertFalse($bean->isSingleton());
    }

    /**
     * @test
     */
    public function markingBeanAsLazy()
    {
        $bean = new Bean(['value' => ['lazy' => true]]);

        $this->assertTrue($bean->isLazy());
    }

    /**
     * @test
     */
    public function markingBeanAsLazyWithString()
    {
        $bean = new Bean(['value' => ['lazy' => 'true']]);

        $this->assertTrue($bean->isLazy());
    }

    /**
     * @test
     */
    public function markingBeanAsLazyWithInt()
    {
        $bean = new Bean(['value' => ['lazy' => 1]]);

        $this->assertTrue($bean->isLazy());
    }

    /**
     * @test
     */
    public function markingBeanAsNonLazy()
    {
        $bean = new Bean(['value' => ['lazy' => false]]);

        $this->assertFalse($bean->isLazy());
    }

    /**
     * @test
     */
    public function markingBeanAsNonLazyWithString()
    {
        $bean = new Bean(['value' => ['lazy' => 'false']]);

        $this->assertFalse($bean->isLazy());
    }

    /**
     * @test
     */
    public function markingBeanAsNonLazyWithInt()
    {
        $bean = new Bean(['value' => ['lazy' => 0]]);

        $this->assertFalse($bean->isLazy());
    }
}
