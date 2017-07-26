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
 *   @Attribute("name", type = "string"),
 *   @Attribute("type", type = "bool"),
 * })
 */
final class Alias
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $type;

    /**
     * Creates a new {@link \bitExpert\Disco\Annotations\Bean\Alias}.
     *
     * @param array $attributes
     * @throws AnnotationException
     */
    public function __construct(array $attributes = [])
    {
        $this->type = false;

        if (isset($attributes['value']['type'])) {
            $this->type = AnnotationAttributeParser::parseBooleanValue($attributes['value']['type']);
        }

        if (isset($attributes['value']['name'])) {
            if ($this->type) {
                throw new AnnotationException('Type alias should not have a name!');
            }

            $this->name = $attributes['value']['name'];
        }

        if (!$this->type && !$this->name) {
            throw new AnnotationException('Alias should either be a named alias or a type alias!');
        }
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function isTypeAlias(): bool
    {
        return $this->type;
    }
}
