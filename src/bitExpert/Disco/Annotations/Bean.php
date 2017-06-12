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
 *   @Attribute("scope", type = "string"),
 *   @Attribute("singleton", type = "bool"),
 *   @Attribute("lazy", type = "bool"),
 *   @Attribute("aliases", type = "array<\bitExpert\Disco\Annotations\Alias>"),
 *   @Attribute("parameters", type = "array<\bitExpert\Disco\Annotations\Parameter>")
 * })
 */
final class Bean
{
    const SCOPE_REQUEST = 1;
    const SCOPE_SESSION = 2;
    /**
     * @var int
     */
    protected $scope;
    /**
     * @var bool
     */
    protected $singleton;
    /**
     * @var bool
     */
    protected $lazy;
    /**
     * @var Alias[]
     */
    protected $aliases;
    /**
     * @var Parameter[]
     */
    protected $parameters;

    /**
     * Creates a new {@link \bitExpert\Disco\Annotations\Bean}.
     *
     * @param array $attributes
     * @throws AnnotationException
     */
    public function __construct(array $attributes = [])
    {
        // initialize default values
        $this->scope = self::SCOPE_REQUEST;
        $this->singleton = true;
        $this->lazy = false;
        $this->aliases = [];
        $this->parameters = [];

        if (isset($attributes['value'])) {
            if (isset($attributes['value']['scope']) && (strtolower($attributes['value']['scope']) === 'session')) {
                $this->scope = self::SCOPE_SESSION;
            }

            if (isset($attributes['value']['singleton'])) {
                $this->singleton = AnnotationAttributeParser::parseBooleanValue($attributes['value']['singleton']);
            }

            if (isset($attributes['value']['lazy'])) {
                $this->lazy = AnnotationAttributeParser::parseBooleanValue($attributes['value']['lazy']);
            }

            if (isset($attributes['value']['aliases'])) {
                $this->aliases = $attributes['value']['aliases'];
            }

            if (isset($attributes['value']['parameters'])) {
                $this->parameters = $attributes['value']['parameters'];
            }
        }
    }

    /**
     * Returns true if the current scope if of type Scope::REQUEST.
     *
     * @return bool
     */
    public function isRequest() : bool
    {
        return $this->scope === self::SCOPE_REQUEST;
    }

    /**
     * Returns true if the current scope if of type Scope::SESSION.
     *
     * @return bool
     */
    public function isSession() : bool
    {
        return $this->scope === self::SCOPE_SESSION;
    }

    /**
     * Returns true if the Bean should be a singleton instance.
     *
     * @return bool
     */
    public function isSingleton() : bool
    {
        return $this->singleton;
    }

    /**
     * Returns true if the Bean should be a lazily instantiated.
     *
     * @return bool
     */
    public function isLazy() : bool
    {
        return $this->lazy;
    }

    /**
     * Returns the list of aliases for the bean instance. Returns an empty array when no alias was set.
     *
     * @return Alias[]
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * Returns the list of parameters for the bean instance. Returns an empty array when no parameters were set.
     *
     * @return Parameter[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
