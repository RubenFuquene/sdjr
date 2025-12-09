# Architecture

## Overview

SDJR is a monorepo containing a full-stack application built with Next.js and Laravel.

## System Components

### Frontend (Next.js)
- Location: `app/frontend/`
- Technology: Next.js 15 with TypeScript
- Features:
  - App Router
  - Tailwind CSS for styling
  - TypeScript for type safety
  - ESLint for code quality

### Backend (Laravel)
- Location: `app/backend/`
- Technology: Laravel 12
- Features:
  - RESTful API
  - Database migrations
  - Authentication
  - Testing with PHPUnit

### Infrastructure
- Location: `app/infra/`
- Technology: Docker Compose
- Services:
  - Frontend container (Next.js)
  - Backend container (Laravel with PHP-FPM)
  - MySQL database
  - Redis cache

## Data Flow

```
Client (Browser) <-> Frontend (Next.js) <-> Backend (Laravel) <-> Database (MySQL)
                                                  |
                                                  v
                                             Cache (Redis)
```

## Deployment

The application uses Docker containers orchestrated with Docker Compose for both development and production environments.

## CI/CD

GitHub Actions workflows automatically:
- Run tests on pull requests
- Lint code
- Build Docker images
- Deploy to staging/production (when configured)
