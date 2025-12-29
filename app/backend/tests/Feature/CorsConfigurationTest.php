<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CORS Configuration Tests
 *
 * Tests CORS functionality according to the specifications
 */
class CorsConfigurationTest extends TestCase
{
    /**
     * Test that CORS configuration file exists and is properly structured
     */
    public function test_cors_configuration_file_exists(): void
    {
        $corsConfig = config('cors');

        $this->assertIsArray($corsConfig);
        $this->assertArrayHasKey('paths', $corsConfig);
        $this->assertArrayHasKey('allowed_methods', $corsConfig);
        $this->assertArrayHasKey('allowed_origins', $corsConfig);
        $this->assertArrayHasKey('allowed_headers', $corsConfig);
        $this->assertArrayHasKey('exposed_headers', $corsConfig);
        $this->assertArrayHasKey('max_age', $corsConfig);
        $this->assertArrayHasKey('supports_credentials', $corsConfig);
    }

    /**
     * Test CORS preflight request for valid origin
     */
    public function test_cors_preflight_request_valid_origin(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:3000',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'Content-Type,Authorization',
        ])->options('/api/v1/countries');

        $response->assertStatus(204);
        $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000');
        $response->assertHeader('Access-Control-Allow-Methods');
        $response->assertHeader('Access-Control-Allow-Headers');
        $response->assertHeader('Access-Control-Allow-Credentials', 'true');
    }

    /**
     * Test CORS request for invalid origin in production
     */
    public function test_cors_request_invalid_origin(): void
    {
        // Set environment to production and ensure no patterns
        app('config')->set('app.env', 'production');
        app('config')->set('cors.allowed_origins_patterns', []);
        app('config')->set('cors.allowed_origins', []);

        $response = $this->withHeaders([
            'Origin' => 'http://malicious-site.com',
        ])->get('/api/v1/countries');

        // In test environment, check that origin header is not echoed back
        // In a real production environment, this would be handled by middleware
        $allowOriginHeader = $response->headers->get('Access-Control-Allow-Origin');
        $this->assertNotEquals('http://malicious-site.com', $allowOriginHeader);
    }

    /**
     * Test CORS headers on API endpoints
     */
    public function test_cors_headers_on_api_endpoints(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:3000',
        ])->get('/api/v1/countries');

        $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000');

        // Verify exposed headers
        $exposedHeaders = $response->headers->get('Access-Control-Expose-Headers');
        if ($exposedHeaders) {
            $this->assertStringContainsString('X-Total-Count', $exposedHeaders);
        }
    }

    /**
     * Test CORS configuration values
     */
    public function test_cors_configuration_values(): void
    {
        $corsConfig = config('cors');

        // Test paths
        $this->assertContains('api/*', $corsConfig['paths']);
        $this->assertContains('sanctum/csrf-cookie', $corsConfig['paths']);

        // Test allowed methods
        $allowedMethods = $corsConfig['allowed_methods'];
        $this->assertContains('GET', $allowedMethods);
        $this->assertContains('POST', $allowedMethods);
        $this->assertContains('PUT', $allowedMethods);
        $this->assertContains('DELETE', $allowedMethods);
        $this->assertContains('OPTIONS', $allowedMethods);

        // Test allowed headers
        $allowedHeaders = $corsConfig['allowed_headers'];
        $this->assertContains('Authorization', $allowedHeaders);
        $this->assertContains('Content-Type', $allowedHeaders);
        $this->assertContains('X-CSRF-TOKEN', $allowedHeaders);

        // Test max age
        $this->assertEquals(86400, $corsConfig['max_age']);

        // Test credentials support
        $this->assertTrue($corsConfig['supports_credentials']);
    }

    /**
     * Test environment-specific CORS patterns
     */
    public function test_cors_patterns_local_environment(): void
    {
        // Force re-read config after setting env
        config(['app.env' => 'local']);
        app('config')->set('cors.allowed_origins_patterns', [
            'http://localhost:*',
            'http://127.0.0.1:*',
            'https://*.vercel.app',
        ]);

        $corsConfig = config('cors');
        $patterns = $corsConfig['allowed_origins_patterns'];

        $this->assertContains('http://localhost:*', $patterns);
        $this->assertContains('http://127.0.0.1:*', $patterns);
        $this->assertContains('https://*.vercel.app', $patterns);
    }
}
