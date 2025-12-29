<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);
        $response = $next($request);
        $this->logAudit($request, $response, $start);

        return $response;
    }

    /**
     * Log the audit data to the database.
     *
     * @param  Response  $response
     */
    protected function logAudit(Request $request, $response, float $start): void
    {
        try {
            $user = Auth::user();
            $payload = $this->sanitizePayload($request->all());
            AuditLog::create([
                'user_id' => $user?->id,
                'method' => $request->method(),
                'endpoint' => $request->path(),
                'payload' => json_encode($payload),
                'response_code' => $response->getStatusCode(),
                'response_time' => (int) ((microtime(true) - $start) * 1000),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Audit log failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'url' => $request->fullUrl(),
            ]);
        }
    }

    /**
     * Sanitize sensitive fields from payload.
     */
    protected function sanitizePayload(array $payload): array
    {
        $sensitive = ['password', 'token', 'access_token', 'credit_card', 'secret'];
        foreach ($payload as $key => &$value) {
            if (in_array($key, $sensitive, true)) {
                $value = '***';
            } elseif (is_array($value)) {
                $value = $this->sanitizePayload($value);
            }
        }

        return $payload;
    }
}
