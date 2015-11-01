<?php

/*
 * This file is part of the Disco package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bitExpert\Disco\Annotations;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class Parameters
{
    /**
     * @var \bitExpert\Disco\Annotations\Parameter[]
     */
    public $value = [];
}
