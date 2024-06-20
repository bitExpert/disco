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

/**
 * Non-repeatable Attribute class to configure the return type as an alias for a Bean.
 *
 * Used in conjunction with the #[Bean] attribute.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class TypeAlias
{
}
