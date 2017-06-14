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

/**
 * Base class for all annotations that are parameter-aware.
 */
abstract class ParameterAwareAnnotation
{
    /**
     * @var Parameter[]
     */
    private $parameters;

    /**
     * Creates a new {@link \bitExpert\Disco\Annotations\ParameterAwareAnnotation}.
     */
    public function __construct()
    {
        $this->parameters = [];
    }

    /**
     * Returns the list of parameters for the bean post processor instance. Returns an empty array when no parameters
     * were set.
     *
     * @return Parameter[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Helper methd to ensure that the passed parameters are of {@link \bitExpert\Disco\Annotations\Parameter} type.
     *
     * @param Parameter[] ...$parameters
     */
    protected function setParameters(Parameter ...$parameters): void
    {
        $this->parameters = $parameters;
    }
}
