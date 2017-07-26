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
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("parameters", type = "array<\bitExpert\Disco\Annotations\Parameter>")
 * })
 */
final class BeanPostProcessor extends ParameterAwareAnnotation
{
    /**
     * Creates a new {@link \bitExpert\Disco\Annotations\BeanPostProcessor}.
     *
     * @param array $attributes
     * @throws AnnotationException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct();

        if (isset($attributes['value']['parameters']) && \is_array($attributes['value']['parameters'])) {
            $this->setParameters(...$attributes['value']['parameters']);
        }
    }
}
