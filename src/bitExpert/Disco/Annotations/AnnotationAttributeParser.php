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

final class AnnotationAttributeParser
{
    /**
     * Helper function to cast a string value to a boolean representation.
     *
     * @param string|bool $value
     * @return bool
     */
    public static function parseBooleanValue($value): bool
    {
        if (\is_bool($value)) {
            return $value;
        }

        if (\is_string($value)) {
            $value = \strtolower($value);
            return 'true' === $value;
        }

        if (\is_object($value) || \is_array($value) || \is_callable($value)) {
            return false;
        }

        // anything else is simply casted to bool
        return (bool) $value;
    }
}
