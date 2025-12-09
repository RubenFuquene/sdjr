# API Documentation

## Base URL

Development: `http://localhost:8000/api`

## Authentication

The API uses Laravel Sanctum for authentication.

### Headers

All authenticated requests should include:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

## Endpoints

### Health Check

#### GET /health
Check if the API is running.

**Response:**
```json
{
  "status": "ok",
  "timestamp": "2024-01-01T00:00:00.000000Z"
}
```

## Error Responses

All error responses follow this format:

```json
{
  "message": "Error message",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

### Common Status Codes

- `200` - Success
- `201` - Created
- `204` - No Content
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Unprocessable Entity (Validation Error)
- `500` - Internal Server Error

## Rate Limiting

API requests are limited to 60 requests per minute per IP address.

## Versioning

The API is versioned through the URL path (e.g., `/api/v1/`).
