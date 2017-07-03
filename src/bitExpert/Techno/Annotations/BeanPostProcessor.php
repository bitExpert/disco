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

namespace bitExpert\Techno\Annotations;

use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Attributes;
use Doctrine\Common\Annotations\AnnotationException;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("parameters", type = "array<\bitExpert\Techno\Annotations\Parameter>")
 * })
 */
final class BeanPostProcessor extends ParameterAwareAnnotation
{

    /**
     * Creates a new {@link \bitExpert\Techno\Annotations\BeanPostProcessor}.
     *
     * @param array $attributes
     * @throws AnnotationException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct();

        if (isset($attributes['value'], $attributes['value']['parameters']) and
            is_array($attributes['value']['parameters'])
        ) {
            $this->setParameters(...$attributes['value']['parameters']);
        }
    }
}
