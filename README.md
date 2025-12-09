# SDJR

A modern full-stack monorepo application built with Next.js and Laravel.

## 🏗️ Project Structure

```
sdjr/
├── app/
│   ├── frontend/          # Next.js application (TypeScript, Tailwind CSS)
│   ├── backend/           # Laravel API (PHP 8.2+)
│   └── infra/            # Docker Compose & infrastructure scripts
│
├── .github/
│   └── workflows/        # CI/CD pipelines for frontend and backend
│
└── docs/                 # Project documentation, diagrams, and specs
```

## 🚀 Quick Start

### Prerequisites

- Node.js 18.x or 20.x
- PHP 8.2+
- Composer
- Docker & Docker Compose

### Setup

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

### Services

- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8000
- **MySQL**: localhost:3306
- **Redis**: localhost:6379

## 📦 Components

### Frontend (`app/frontend`)
- **Framework**: Next.js 15 with App Router
- **Language**: TypeScript
- **Styling**: Tailwind CSS
- **Linting**: ESLint

### Backend (`app/backend`)
- **Framework**: Laravel 12
- **Language**: PHP 8.2+
- **Database**: MySQL 8.0
- **Cache**: Redis
- **Testing**: PHPUnit

### Infrastructure (`app/infra`)
- **Orchestration**: Docker Compose
- **Scripts**: Setup, development, and cleanup utilities

## 🧪 Development

### Frontend Development
```bash
cd app/frontend
npm install
npm run dev
npm run lint
npm run build
```

### Backend Development
```bash
cd app/backend
composer install
php artisan serve
php artisan test
./vendor/bin/pint
```

## 🔄 CI/CD

Automated workflows via GitHub Actions:
- Frontend CI: Linting, testing, and building
- Backend CI: Testing, linting (Pint), and building

## 📚 Documentation

Detailed documentation is available in the [`docs/`](./docs) directory:
- [Architecture Overview](./docs/architecture.md)
- [Development Guide](./docs/development.md)
- [API Documentation](./docs/api.md)

## 🤝 Contributing

1. Create a feature branch
2. Make your changes
3. Write/update tests
4. Run linters and tests
5. Submit a pull request

## 📄 License

This project is private and proprietary.