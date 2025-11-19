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

The first time you run this, Docker will build the images which may take a few minutes.

### Viewing logs

```bash
cd app/infra
docker-compose logs -f
```

Or for a specific service:
```bash
docker-compose logs -f backend
```

### Stopping the development environment

```bash
cd app/infra
docker-compose down
```

### Rebuilding after changes

If you make changes to the Dockerfile or dependencies:
```bash
cd app/infra
docker-compose up -d --build
```

## Services

- **frontend**: Next.js application (port 3000)
- **backend**: Laravel API (port 8000) - Auto-configures on first run
- **db**: MySQL database (port 3306)
- **redis**: Redis cache (port 6379)

## Backend Notes

The backend container automatically:
- Creates `.env` from `.env.example` if not present
- Generates `APP_KEY` if not set
- Runs database migrations
- Starts Laravel development server on port 8000

Access the backend at: http://localhost:8000
