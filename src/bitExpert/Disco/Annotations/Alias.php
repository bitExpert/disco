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

use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Attributes;
use Doctrine\Common\Annotations\AnnotationException;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 * @Attributes({
 *   @Attribute("name", type = "string")
 * })
 */
final class Alias
{
    /**
     * @var string
     */
    private $name;

    /**
     * Creates a new {@link \bitExpert\Disco\Annotations\Bean\Alias}.
     *
     * @param array $attributes
     * @throws AnnotationException
     */
    public function __construct(array $attributes = [])
    {
        $this->name = null;

        if (!isset($attributes['value'])) {
            throw new AnnotationException("No attributes value passed to " . __METHOD__);
        }

        if(!isset($attributes['value']['name'])) {
            throw new AnnotationException("Alias name missing");
        }

        $this->name = $attributes['value']['name'];
    }

    public function getName(): string
    {
        return $this->name;
    }
}
