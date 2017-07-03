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

namespace bitExpert\Techno;

use Psr\Container\ContainerExceptionInterface;

/**
 * Superclass for all exceptions thrown in the \bitExpert\Techno package and it`s
 * subpackages.
 */
class BeanException extends \RuntimeException implements ContainerExceptionInterface
{
}
