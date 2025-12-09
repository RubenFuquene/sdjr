# Development Guide

## Prerequisites

- Node.js 18.x or 20.x
- PHP 8.2 or higher
- Composer
- Docker and Docker Compose
- Git

## Initial Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/RubenFuquene/sdjr.git
   cd sdjr
   ```

2. Run the setup script:
   ```bash
   cd app/infra
   ./scripts/setup.sh
   ```

3. Start the development environment:
   ```bash
   ./scripts/dev.sh
   ```

## Development Workflow

### Frontend Development

```bash
cd app/frontend
npm install
npm run dev
```

The frontend will be available at http://localhost:3000

### Backend Development

```bash
cd app/backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

The backend API will be available at http://localhost:8000

### Using Docker

Start all services:
```bash
cd app/infra
docker-compose up -d
```

View logs:
```bash
docker-compose logs -f
```

Stop services:
```bash
docker-compose down
```

## Testing

### Frontend Tests
```bash
cd app/frontend
npm test
```

### Backend Tests
```bash
cd app/backend
php artisan test
```

## Code Quality

### Frontend Linting
```bash
cd app/frontend
npm run lint
```

### Backend Linting
```bash
cd app/backend
./vendor/bin/pint
```

## Project Structure

```
sdjr/
├── app/
│   ├── frontend/              # Next.js application
│   │   ├── src/
│   │   │   ├── app/          # App Router pages
│   │   │   └── components/   # React components
│   │   ├── public/           # Static assets
│   │   └── package.json
│   │
│   ├── backend/              # Laravel application
│   │   ├── app/             # Application logic
│   │   ├── database/        # Migrations and seeders
│   │   ├── routes/          # API routes
│   │   └── composer.json
│   │
│   └── infra/               # Infrastructure
│       ├── docker-compose.yml
│       └── scripts/         # Utility scripts
│
├── .github/
│   └── workflows/           # CI/CD workflows
│       ├── frontend-ci.yml
│       └── backend-ci.yml
│
└── docs/                    # Documentation
    ├── README.md
    ├── architecture.md
    └── development.md
```

## Contributing

1. Create a new branch for your feature
2. Make your changes
3. Write tests
4. Run linters and tests
5. Submit a pull request

## Troubleshooting

### Port conflicts
If you get port conflict errors, check that ports 3000, 8000, 3306, and 6379 are not in use.

### Database connection issues
Make sure the MySQL service is running and the credentials in `.env` match the docker-compose configuration.

### Node modules issues
Try removing `node_modules` and `package-lock.json`, then run `npm install` again.
