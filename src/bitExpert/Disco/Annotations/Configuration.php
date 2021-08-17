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
 * Non-repeatable Attribute to mark a class a Beans or BeanPostProcessors provider.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Configuration
{
}
