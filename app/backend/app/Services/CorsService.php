<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * CORS Service for enhanced logging and monitoring
 * Implements CORS event logging as specified in the technical specifications
 */
class CorsService
{
    /**
     * Log CORS request for monitoring purposes
     * 
     * @param Request $request
     * @param string $status (allowed|blocked)
     * @return void
     */
    public static function logCorsRequest(Request $request, string $status = 'allowed'): void
    {
        try {
            $corsData = [
                'origin' => $request->header('Origin'),
                'method' => $request->method(),
                'endpoint' => $request->path(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => $status,
                'timestamp' => now()->toISOString()
            ];

            if ($status === 'blocked') {
                Log::warning('CORS request blocked', $corsData);
            } else {
                Log::info('CORS request processed', $corsData);
            }
        } catch (\Exception $e) {
            Log::error('Failed to log CORS request', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Check if origin is allowed based on environment configuration
     * 
     * @param string|null $origin
     * @return bool
     */
    public static function isOriginAllowed(?string $origin): bool
    {
        if (!$origin) {
            return false;
        }

        $allowedOrigins = explode(',', env('CORS_ALLOWED_ORIGINS', ''));
        
        // Check exact match
        if (in_array($origin, $allowedOrigins)) {
            return true;
        }

        // Check patterns for local environment
        if (env('APP_ENV') === 'local') {
            $patterns = [
                'http://localhost:*',
                'http://127.0.0.1:*',
                'https://*.vercel.app'
            ];

            foreach ($patterns as $pattern) {
                if (self::matchesPattern($origin, $pattern)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if origin matches a pattern
     * 
     * @param string $origin
     * @param string $pattern
     * @return bool
     */
    private static function matchesPattern(string $origin, string $pattern): bool
    {
        // Convert pattern to regex
        $regex = str_replace(['*', '.'], ['.*', '\.'], $pattern);
        $regex = '/^' . $regex . '$/i';
        
        return preg_match($regex, $origin) === 1;
    }
}