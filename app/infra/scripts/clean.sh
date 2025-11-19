#!/bin/bash

# SDJR Clean Script
# Cleans up Docker containers, volumes, and build artifacts

set -e

echo "🧹 Cleaning SDJR development environment..."

cd "$(dirname "$0")/.."

# Stop and remove containers, networks, and volumes
docker compose down -v

echo "✅ Cleanup complete!"
