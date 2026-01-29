# CORS Configuration Specification - SDJR Platform

**Namespace:** cors-configuration  
**Version:** 1.0.0  
**Date:** 2025-12-23  
**Analyst:** Backend Analyst Senior  

---

## 1. EXECUTIVE SUMMARY

### Problem Statement
La aplicación SDJR requiere una configuración CORS robusta y segura para permitir la comunicación entre el frontend Next.js (localhost:3000) y el backend Laravel 12 (localhost:8000), incluyendo soporte para autenticación con tokens y preparación para deployment en producción.

### Solution Overview
Implementación de configuración CORS nativa de Laravel con políticas diferenciadas para desarrollo y producción, optimizada para seguridad y performance.

---

## 2. ARQUITECTURA CORS

### 2.1 Current State Analysis
```
Status Actual:
- Laravel 12 con fruitcake/php-cors v1.4.0 (detectado en composer.lock)
- HandleCors middleware presente pero no configurado específicamente
- Sanctum configurado para autenticación API
- Sin configuración CORS explícita

Gaps Identificados:
- Ausencia de config/cors.php
- Middleware CORS no registrado en bootstrap/app.php
- Variables de entorno CORS no definidas
- Headers de seguridad no optimizados
```

### 2.2 Target Architecture
```
Frontend (localhost:3000 | vercel.app) 
    ↓ [CORS Headers]
API Gateway (localhost:8000 | domain.com)
    ↓ [Authentication via Sanctum]
Backend Services (Laravel Controllers)
```

---

## 3. PILAR 1: MODELO DE DATOS CORS

### 3.1 CORS Configuration Schema
```php
// Config Structure Schema
[
    'paths' => string[], // API routes que requieren CORS
    'allowed_methods' => string[], // HTTP methods permitidos
    'allowed_origins' => string[], // Origins permitidos
    'allowed_origins_patterns' => string[], // Patterns para origins dinámicos
    'allowed_headers' => string[], // Headers permitidos
    'exposed_headers' => string[], // Headers expuestos al cliente
    'max_age' => int, // Cache time para preflight requests
    'supports_credentials' => bool, // Soporte para cookies/credentials
]
```

### 3.2 Environment Variables Schema
```env
# CORS Configuration
CORS_ALLOWED_ORIGINS=string
CORS_ALLOWED_METHODS=string
CORS_ALLOWED_HEADERS=string
CORS_EXPOSED_HEADERS=string
CORS_MAX_AGE=integer
CORS_SUPPORTS_CREDENTIALS=boolean

# Environment Specific
FRONTEND_URL=string
FRONTEND_PROD_URL=string
```

---

## 4. PILAR 2: LÓGICA ATÓMICA CORS

### 4.1 Core CORS Logic
```php
// Atomic CORS Operations

1. Origin Validation
   - Validate against allowed_origins list
   - Support for wildcard patterns in development
   - Strict domain matching in production

2. Method Validation
   - Allow specific HTTP methods per endpoint
   - Block dangerous methods in production

3. Header Processing
   - Process preflight OPTIONS requests
   - Validate and sanitize custom headers
   - Set security headers

4. Credential Handling
   - Control cookie transmission
   - Manage authentication headers
   - Handle Sanctum token validation
```

### 4.2 Request Flow Logic
```
1. Incoming Request → CORS Middleware
2. Origin Check → Allowed/Denied
3. Method Validation → Proceed/Block
4. Preflight Handling → OPTIONS Response
5. Header Processing → Set CORS Headers
6. Continue to Authentication → Sanctum
```

---

## 5. PILAR 3: ESTANDARIZACIÓN HTTP

### 5.1 HTTP Headers Standard
```http
# Request Headers (Client → Server)
Origin: http://localhost:3000
Access-Control-Request-Method: POST
Access-Control-Request-Headers: Content-Type, Authorization

# Response Headers (Server → Client)
Access-Control-Allow-Origin: http://localhost:3000
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With
Access-Control-Allow-Credentials: true
Access-Control-Expose-Headers: X-Total-Count, X-Rate-Limit-Remaining
Access-Control-Max-Age: 86400
```

### 5.2 HTTP Status Codes
```http
200 OK - CORS request successful
204 No Content - Preflight request successful
403 Forbidden - Origin not allowed
405 Method Not Allowed - Method not supported
```

### 5.3 API Endpoints Requiring CORS
```php
// Authentication Endpoints
POST /api/v1/login

// Protected Resource Endpoints (require CORS + Auth)
GET|POST|PUT|DELETE /api/v1/countries
GET|POST|PUT|DELETE /api/v1/departments
GET|POST|PUT|DELETE /api/v1/cities
GET|POST|PUT|DELETE /api/v1/categories
GET|POST|PUT|DELETE /api/v1/neighborhoods
GET|POST|PUT|DELETE /api/v1/establishment-types
GET|POST|PUT|DELETE /api/v1/users
GET|POST|PUT|DELETE /api/v1/commerces
GET /api/v1/audit-logs
```

---

## 6. PILAR 4: REQUERIMIENTOS NO FUNCIONALES

### 6.1 Security Requirements
```yaml
Security Level: HIGH
- Origin Validation: Strict whitelist approach
- Method Restriction: Only required HTTP methods
- Header Sanitization: Validate all custom headers
- Credential Security: Controlled cookie transmission
- Production Hardening: No wildcards in production

Rate Limiting Integration:
- CORS preflight requests counted separately
- Authentication rate limits applied post-CORS
- Failed CORS requests logged for security monitoring
```

### 6.2 Performance Requirements
```yaml
Performance Targets:
- CORS Header Processing: < 2ms
- Preflight Cache: 24 hours (86400 seconds)
- Memory Overhead: < 1MB per request
- Browser Cache Optimization: Maximize preflight caching

Optimization Strategies:
- Minimal header set
- Efficient origin matching
- Cached configuration loading
```

### 6.3 Environment-Specific Requirements
```yaml
Development:
- Origins: localhost:3000, 127.0.0.1:3000
- Methods: All HTTP methods allowed
- Headers: Permissive header policy
- Credentials: Enabled for testing

Production:
- Origins: Specific domain whitelist only
- Methods: Restricted to necessary methods
- Headers: Minimal required headers
- Credentials: Carefully controlled
```

---

## 7. PILAR 5: FORMATO MACHINE-READABLE

### 7.1 Configuration Implementation

#### config/cors.php
```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie'
    ],

    'allowed_methods' => [
        'GET',
        'POST', 
        'PUT',
        'PATCH',
        'DELETE',
        'OPTIONS'
    ],

    'allowed_origins' => env('CORS_ALLOWED_ORIGINS') ? 
        explode(',', env('CORS_ALLOWED_ORIGINS')) : [],

    'allowed_origins_patterns' => env('APP_ENV') === 'local' ? [
        'http://localhost:*',
        'http://127.0.0.1:*',
        'https://*.vercel.app'
    ] : [],

    'allowed_headers' => [
        'Accept',
        'Authorization',
        'Content-Type',
        'X-Requested-With',
        'X-CSRF-TOKEN',
        'X-Socket-Id'
    ],

    'exposed_headers' => [
        'X-Total-Count',
        'X-Rate-Limit-Remaining',
        'X-Rate-Limit-Limit'
    ],

    'max_age' => (int) env('CORS_MAX_AGE', 86400),

    'supports_credentials' => (bool) env('CORS_SUPPORTS_CREDENTIALS', true),
];
```

#### Environment Variables (.env)
```env
# CORS Configuration
CORS_ALLOWED_ORIGINS=http://localhost:3000
CORS_MAX_AGE=86400
CORS_SUPPORTS_CREDENTIALS=true

# Frontend URLs
FRONTEND_URL=http://localhost:3000
FRONTEND_PROD_URL=https://sdjr-frontend.vercel.app

# Sanctum Configuration (CORS related)
SANCTUM_STATEFUL_DOMAINS=localhost:3000,sdjr-frontend.vercel.app
SESSION_DOMAIN=localhost
```

#### Production Environment (.env.production)
```env
# Production CORS Configuration
CORS_ALLOWED_ORIGINS=https://sdjr-frontend.vercel.app,https://www.sdjr.com
CORS_MAX_AGE=86400
CORS_SUPPORTS_CREDENTIALS=true

# Production URLs
FRONTEND_URL=https://sdjr-frontend.vercel.app
FRONTEND_PROD_URL=https://www.sdjr.com

# Sanctum Configuration
SANCTUM_STATEFUL_DOMAINS=sdjr-frontend.vercel.app,www.sdjr.com
SESSION_DOMAIN=.sdjr.com
```

### 7.2 Middleware Registration

#### bootstrap/app.php Update
```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global middleware
        $middleware->use([
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        
        // API middleware group
        $middleware->group('api', [
            \App\Http\Middleware\AuditMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
```

---

## 8. TESTING PROCEDURES

### 8.1 CORS Testing Matrix
```http
# Test 1: Valid Origin Request
curl -H "Origin: http://localhost:3000" \
     -H "Access-Control-Request-Method: POST" \
     -H "Access-Control-Request-Headers: Content-Type,Authorization" \
     -X OPTIONS \
     http://localhost:8000/api/v1/login

Expected Response:
HTTP/1.1 204 No Content
Access-Control-Allow-Origin: http://localhost:3000
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With
Access-Control-Allow-Credentials: true

# Test 2: Invalid Origin Request  
curl -H "Origin: http://malicious-site.com" \
     -X OPTIONS \
     http://localhost:8000/api/v1/login

Expected Response:
HTTP/1.1 403 Forbidden
```

### 8.2 Authentication Flow Testing
```http
# Test 3: Login with CORS
curl -H "Origin: http://localhost:3000" \
     -H "Content-Type: application/json" \
     -X POST \
     -d '{"email":"user@example.com","password":"password"}' \
     http://localhost:8000/api/v1/login

# Test 4: Protected Resource with Token
curl -H "Origin: http://localhost:3000" \
     -H "Authorization: Bearer {token}" \
     -X GET \
     http://localhost:8000/api/v1/users
```

### 8.3 Frontend Integration Testing
```javascript
// Test 5: Next.js Fetch with CORS
const response = await fetch('http://localhost:8000/api/v1/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  credentials: 'include',
  body: JSON.stringify({ email: 'user@example.com', password: 'password' })
});

// Expected: Success response with proper CORS headers
```

---

## 9. SECURITY CONSIDERATIONS

### 9.1 Development vs Production Security
```yaml
Development Security:
- Relaxed origins for local testing
- All HTTP methods allowed
- Detailed error responses
- CORS debugging enabled

Production Security:
- Strict domain whitelist
- Method restriction per endpoint
- Minimal error information
- Security monitoring enabled
```

### 9.2 Security Headers Integration
```http
# Additional Security Headers (future enhancement)
Content-Security-Policy: default-src 'self'
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
```

---

## 10. MONITORING & LOGGING

### 10.1 CORS Event Logging
```php
// Log CORS events for monitoring
'cors_request_blocked' => [
    'origin' => $request->header('Origin'),
    'method' => $request->method(),
    'endpoint' => $request->path(),
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent()
]
```

### 10.2 Metrics Collection
- CORS requests per minute
- Failed CORS attempts by origin
- Preflight cache hit ratio
- Authentication success rate post-CORS

---

## 11. DEPLOYMENT CHECKLIST

### 11.1 Pre-deployment Validation
- [ ] config/cors.php created and configured
- [ ] Environment variables set correctly
- [ ] Middleware registered in bootstrap/app.php
- [ ] Sanctum domains configured
- [ ] Testing matrix completed
- [ ] Security review passed

### 11.2 Post-deployment Verification
- [ ] Frontend can authenticate successfully
- [ ] All API endpoints accessible
- [ ] Preflight requests cached properly
- [ ] No unauthorized origins accepted
- [ ] Performance metrics within targets

---

## 12. FUTURE ENHANCEMENTS

### 12.1 Advanced CORS Features
- Dynamic origin validation based on user tenant
- Request rate limiting per origin
- Advanced security headers integration
- CORS analytics dashboard

### 12.2 Microservices Preparation
- Service mesh CORS configuration
- Cross-service authentication headers
- Distributed CORS policy management

---

**Document Control:**
- **Created:** 2025-12-23
- **Last Modified:** 2025-12-23
- **Next Review:** 2025-01-23
- **Approved By:** Backend Analyst Senior
- **Status:** Ready for Implementation

---

*This specification follows the 5-pillar methodology: Data Model, Atomic Logic, HTTP Standards, Non-functional Requirements, and Machine-readable Format.*