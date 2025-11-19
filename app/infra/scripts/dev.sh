#!/bin/bash

# SDJR Development Script
# Quick start script for local development

set -e

echo "🚀 Starting SDJR development environment..."

cd "$(dirname "$0")/.."

echo "🏗️ Building Docker images (this might take a while the first time)..."
docker compose build

# Start Docker containers
echo "⬆️ Starting containers..."
docker compose up -d

echo "✅ Development environment is running!"
echo ""
echo "Services:"
echo "  Frontend: http://localhost:3000"
echo "  Backend:  http://localhost:8000"
echo "  MySQL:    localhost:3306"
echo "  Redis:    localhost:6379"
echo ""
echo "To view logs, run: docker compose logs -f"
echo "To stop, run: docker compose down"
