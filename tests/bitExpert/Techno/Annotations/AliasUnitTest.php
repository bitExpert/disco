<?php

/*
 * This file is part of the Techno package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace bitExpert\Techno\Annotations;

use Doctrine\Common\Annotations\AnnotationException;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for {@link \bitExpert\Techno\Annotations\Alias}.
 */
class AliasUnitTest extends TestCase
{
    /**
     * @test
     */
    public function aliasCanBeNamedAlias()
    {
        $namedAlias = new Alias(['value' => ['name' => 'someAliasName']]);

        self::assertSame('someAliasName', $namedAlias->getName());
        self::assertFalse($namedAlias->isTypeAlias());
    }

    /**
     * @test
     */
    public function aliasCannotBeNamedAliasAndTypeAlias()
    {
        self::expectException(AnnotationException::class);
        self::expectExceptionMessage('Type alias should not have a name!');
        new Alias(['value' => ['name' => 'someAliasName', 'type' => true]]);
    }

    /**
     * @test
     */
    public function aliasCanBeTypeAlias()
    {
        $typeAlias = new Alias(['value' => ['type' => true]]);

        self::assertTrue($typeAlias->isTypeAlias());
        self::assertNull($typeAlias->getName());
    }

    /**
     * @test
     */
    public function aliasShouldBeNamedOrTypeAlias()
    {
        self::expectException(AnnotationException::class);
        self::expectExceptionMessage("Alias should either be a named alias or a type alias!");
        new Alias();
    }
}
