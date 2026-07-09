<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Resolves absolute URLs for images used in transactional email views.
 *
 * Email clients cannot resolve relative paths, so images are hosted on the
 * frontend's public folder (same mechanism as the brand header logo) and
 * referenced by absolute URL.
 */
class EmailAsset
{
    /**
     * Build the absolute URL for an image under public/brand/emails on the frontend.
     */
    public static function url(string $file): string
    {
        $baseUrl = rtrim((string) config('app.frontend_prod_url'), '/');

        return $baseUrl.'/brand/emails/'.ltrim($file, '/');
    }
}
