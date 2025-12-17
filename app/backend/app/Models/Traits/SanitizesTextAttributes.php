<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait SanitizesTextAttributes
{
    /**
     * Sanitize a text attribute: trim, ucfirst, lower, UTF-8 safe.
     *
     * @param string|null $value
     * @return string|null
     */
    protected function sanitizeText(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        $value = trim($value);
        return Str::of($value)
            ->lower()
            ->ucfirst()
            ->toString();
    }

    /**
     * Capitalize each word in a text attribute: trim, title case, UTF-8 safe.
     *
     * @param string|null $value
     * @return string|null
     */
    protected function capitalizeText(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        $value = trim($value);
        return Str::of($value)
            ->title()
            ->toString();
    }

    /**
     * Sanitize email attribute: trim, lower case, UTF-8 safe.
     *
     * @param string|null $value
     * @return string|null
     */
    protected function sanitizeEmail(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        return trim(Str::lower($value));
    }

    /**
     * Sanitize phone attribute: trim.
     *
     * @param string|null $value
     * @return string|null
     */
    protected function sanitizePhone(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        return trim($value);
    }
}
