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

use Attribute;
use Webmozart\Assert\Assert;

/**
 * Repeatable Attribute to declare a "configuration key to parameter mapping" of a Bean or BeanPostProcessor factory
 * method.
 *
 * Used in conjunction with the #[Bean] or #[BeanPostProcessor] attribute.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Parameter
{
    private string $key;

    private ?string $name;

    private mixed $defaultValue;

    private bool $required;

    /**
     * @param string $key
     * @param bool $required
     * @param mixed $default
     * @param string|null $name
     */
    public function __construct(string $key, bool $required = true, mixed $default = null, ?string $name = null)
    {
        Assert::minLength($key, 1);
        Assert::nullOrMinLength($name, 1);

        $this->key = $key;
        $this->name = $name;
        $this->defaultValue = $default;
        $this->required = $required;
    }

    /**
     * Return the name of the argument or null in case of a positioned argument
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Returns the key of the configuration value to use.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Returns the default value to use in case the configuration value is not defined.
     *
     * @return mixed
     */
    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    /**
     * Returns true if the parameter is required, false for an optional parameter.
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }
}
