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
 * Unit tests for {@link \bitExpert\Disco\Annotations\Alias}.
 */
class AliasUnitTest extends TestCase
{
    /**
     * @test
     */
    public function aliasCanBeNamedAlias(): void
    {
        $namedAlias = new Alias(['value' => ['name' => 'someAliasName']]);

        self::assertSame('someAliasName', $namedAlias->getName());
        self::assertFalse($namedAlias->isTypeAlias());
    }

    /**
     * @test
     */
    public function aliasCannotBeNamedAliasAndTypeAlias(): void
    {
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage('Type alias should not have a name!');

        new Alias(['value' => ['name' => 'someAliasName', 'type' => true]]);
    }

    /**
     * @test
     */
    public function aliasCanBeTypeAlias(): void
    {
        $typeAlias = new Alias(['value' => ['type' => true]]);

        self::assertTrue($typeAlias->isTypeAlias());
        self::assertNull($typeAlias->getName());
    }

    /**
     * @test
     */
    public function aliasShouldBeNamedOrTypeAlias(): void
    {
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage('Alias should either be a named alias or a type alias!');

        new Alias();
    }

    /**
     * @test
     * @dataProvider invalidNameProvider
     * @param mixed $name
     */
    public function aliasNameCannotBeEmpty(mixed $name): void
    {
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage('Alias should either be a named alias or a type alias!');

        new Alias(['value' => ['name' => $name, 'type' => false]]);
    }

    /**
     * @return array<mixed>
     */
    public function invalidNameProvider(): array
    {
        return [
            [''],
            [0],
            [0.0],
            [false],
            [null],
            [[]],
        ];
    }
}
