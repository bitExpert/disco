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

use Attribute;
use Webmozart\Assert\Assert;

/**
 * Repeatable Attribute class to configure named aliases for a Bean.
 *
 * Used in conjunction with the #[Bean] attribute.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Alias
{
    /**
     * @var string
     */
    private string $name;

    /**
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        Assert::minLength($name, 1);

        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
