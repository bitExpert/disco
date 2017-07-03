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

use Psr\Container\NotFoundExceptionInterface;

/**
 * Exception being thrown if called / referenced bean does not exist in the
 * {@link \bitExpert\Techno\BeanFactory}.
 */
class BeanNotFoundException extends BeanException implements NotFoundExceptionInterface
{
}
