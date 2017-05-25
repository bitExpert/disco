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

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for {@link \bitExpert\Disco\BeanFactoryAwareTrait}.
 */
class BeanFactoryAwareTraitUnitTest extends TestCase
{
    /**
     * @test
     */
    public function setBeanFactory()
    {
        $beanFactory = $this->createMock(BeanFactory::class);
        $trait = $this->getMockForTrait(BeanFactoryAwareTrait::class);

        $trait->setBeanFactory($beanFactory);

        self::assertAttributeInstanceOf(BeanFactory::class, 'beanFactory', $trait);
        self::assertAttributeSame($beanFactory, 'beanFactory', $trait);
    }
}
