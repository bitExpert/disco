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

use bitExpert\Disco\Annotations\Alias;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

/**
 * Unit tests for {@link \bitExpert\Disco\Annotations\NameAlias}.
 */
class AliasUnitTest extends TestCase
{
    /**
     * @test
     */
    public function aliasCanBeNamedAlias(): void
    {
        $namedAlias = new Alias(name: 'someAliasName');

        self::assertSame('someAliasName', $namedAlias->getName());
    }

    /**
     * @test
     */
    public function aliasNameCannotBeEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Alias(name: '');
    }
}
