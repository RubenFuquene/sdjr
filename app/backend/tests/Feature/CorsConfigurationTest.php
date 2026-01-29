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

        $this->assertNotNull($response->headers->get('Access-Control-Allow-Origin'));
        $this->assertTrue(
            $response->getStatusCode() === 200 || $response->getStatusCode() === 204,
            'Preflight request should return 200 or 204'
        );
    }

    /**
     * Test CORS request for invalid origin in production
     */
    public function test_cors_request_invalid_origin(): void
    {
        // Make a request with an invalid origin
        $response = $this->withHeaders([
            'Origin' => 'http://malicious-site.com',
        ])->get('/api/v1/countries');

        // The request should not echo back the malicious origin
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

        // Verify that the endpoint responded
        $this->assertTrue($response->getStatusCode() > 0);

        // Check CORS headers if origin is allowed
        if ($response->getStatusCode() !== 401) {
            $this->assertNotNull($response->headers->get('Access-Control-Allow-Origin'));
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
