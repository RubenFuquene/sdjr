#!/bin/bash

# SDJR Monorepo Setup Script
# This script sets up the development environment for the SDJR project

set -e

echo "🚀 Setting up SDJR development environment..."

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker first."
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

echo "✅ Docker and Docker Compose are installed"

# Setup Frontend
echo "📦 Setting up Frontend..."
cd ../frontend
if [ ! -d "node_modules" ]; then
    npm install
fi

# Setup Backend
echo "📦 Setting up Backend..."
cd ../backend
if [ ! -f ".env" ]; then
    cp .env.example .env
    echo "✅ Created .env file for backend"
fi

if [ ! -d "vendor" ]; then
    composer install
fi

# Generate application key if not set
if ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate
fi

echo "✅ Setup complete!"
echo ""
echo "To start the development environment, run:"
echo "  cd app/infra"
echo "  docker-compose up -d"
