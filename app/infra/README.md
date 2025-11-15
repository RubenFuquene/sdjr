# Infrastructure

This directory contains infrastructure configuration files and scripts for the SDJR monorepo.

## Contents

- `docker-compose.yml`: Docker Compose configuration for local development
- `scripts/`: Utility scripts for development and deployment

## Usage

### Starting the development environment

```bash
cd app/infra
docker-compose up -d
```

### Stopping the development environment

```bash
cd app/infra
docker-compose down
```

## Services

- **frontend**: Next.js application (port 3000)
- **backend**: Laravel API (port 8000)
- **db**: MySQL database (port 3306)
- **redis**: Redis cache (port 6379)
