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
}
