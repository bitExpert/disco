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

use Psr\Container\ContainerExceptionInterface;

/**
 * Superclass for all exceptions thrown in the \bitExpert\Disco package and it`s
 * subpackages.
 */
class BeanException extends \RuntimeException implements ContainerExceptionInterface
{
}
