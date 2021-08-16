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

namespace bitExpert\Disco\Attributes;

use Attribute;
use Webmozart\Assert\Assert;

/**
 * Non-repeatable Attribute to declare a method as a Bean factory.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class Bean
{
    public const SCOPE_REQUEST = 1;
    public const SCOPE_SESSION = 2;

    private int $scope;

    private bool $singleton;

    private bool $lazy;

    /**
     * @param bool $singleton
     * @param bool $lazy
     * @param int $scope
     */
    public function __construct(bool $singleton = true, bool $lazy = false, int $scope = self::SCOPE_REQUEST)
    {
        Assert::inArray($scope, [self::SCOPE_REQUEST, self::SCOPE_SESSION]);

        $this->singleton = $singleton;
        $this->lazy = $lazy;
        $this->scope = $scope;
    }

    /**
     * Returns true if the current scope if of type Scope::REQUEST.
     *
     * @return bool
     */
    public function isRequest(): bool
    {
        return $this->scope === self::SCOPE_REQUEST;
    }

    /**
     * Returns true if the current scope if of type Scope::SESSION.
     *
     * @return bool
     */
    public function isSession(): bool
    {
        return $this->scope === self::SCOPE_SESSION;
    }

    /**
     * Returns true if the Bean should be a singleton instance.
     *
     * @return bool
     */
    public function isSingleton(): bool
    {
        return $this->singleton;
    }

    /**
     * Returns true if the Bean should be a lazily instantiated.
     *
     * @return bool
     */
    public function isLazy(): bool
    {
        return $this->lazy;
    }
}
