#!/bin/bash

# CORS Configuration Validation Script
# This script validates that CORS is properly configured according to specifications

echo "🚀 SDJR Backend - CORS Configuration Validation"
echo "================================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "\n${BLUE}1. Checking CORS Configuration File...${NC}"

if [ -f "config/cors.php" ]; then
    echo -e "${GREEN}✅ config/cors.php exists${NC}"
else
    echo -e "${RED}❌ config/cors.php not found${NC}"
    exit 1
fi

echo -e "\n${BLUE}2. Checking Environment Variables...${NC}"

# Check if .env has CORS variables
if grep -q "CORS_ALLOWED_ORIGINS" .env; then
    echo -e "${GREEN}✅ CORS_ALLOWED_ORIGINS configured${NC}"
else
    echo -e "${RED}❌ CORS_ALLOWED_ORIGINS not found in .env${NC}"
fi

if grep -q "SANCTUM_STATEFUL_DOMAINS" .env; then
    echo -e "${GREEN}✅ SANCTUM_STATEFUL_DOMAINS configured${NC}"
else
    echo -e "${RED}❌ SANCTUM_STATEFUL_DOMAINS not found in .env${NC}"
fi

echo -e "\n${BLUE}3. Testing CORS Preflight Request...${NC}"

# Test preflight request
PREFLIGHT_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" \
    -H "Origin: http://localhost:3000" \
    -H "Access-Control-Request-Method: POST" \
    -H "Access-Control-Request-Headers: Content-Type,Authorization" \
    -X OPTIONS \
    http://localhost:8000/api/v1/countries 2>/dev/null || echo "000")

if [ "$PREFLIGHT_RESPONSE" = "200" ] || [ "$PREFLIGHT_RESPONSE" = "204" ]; then
    echo -e "${GREEN}✅ CORS Preflight Request: Success (HTTP $PREFLIGHT_RESPONSE)${NC}"
else
    echo -e "${YELLOW}⚠️  CORS Preflight Request: HTTP $PREFLIGHT_RESPONSE (Server may not be running)${NC}"
fi

echo -e "\n${BLUE}4. Testing Actual CORS Request...${NC}"

# Test actual CORS request
CORS_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" \
    -H "Origin: http://localhost:3000" \
    -X GET \
    http://localhost:8000/api/v1/countries 2>/dev/null || echo "000")

if [ "$CORS_RESPONSE" = "200" ] || [ "$CORS_RESPONSE" = "401" ]; then
    echo -e "${GREEN}✅ CORS Request: Success (HTTP $CORS_RESPONSE)${NC}"
else
    echo -e "${YELLOW}⚠️  CORS Request: HTTP $CORS_RESPONSE (Server may not be running)${NC}"
fi

echo -e "\n${BLUE}5. Checking Middleware Registration...${NC}"

if grep -q "HandleCors" bootstrap/app.php; then
    echo -e "${GREEN}✅ CORS Middleware registered in bootstrap/app.php${NC}"
else
    echo -e "${RED}❌ CORS Middleware not found in bootstrap/app.php${NC}"
fi

echo -e "\n${BLUE}6. Configuration Summary:${NC}"
echo "========================="

if [ -f ".env" ]; then
    echo -e "${YELLOW}Development Environment:${NC}"
    grep -E "CORS_|FRONTEND_|SANCTUM_" .env 2>/dev/null | head -6
fi

if [ -f ".env.production" ]; then
    echo -e "\n${YELLOW}Production Environment:${NC}"
    grep -E "CORS_|FRONTEND_|SANCTUM_" .env.production 2>/dev/null | head -6
fi

echo -e "\n${GREEN}🎉 CORS Configuration Validation Complete!${NC}"
echo -e "\n${YELLOW}Next Steps:${NC}"
echo "1. Start the Laravel server: php artisan serve"
echo "2. Start the frontend: cd ../frontend && npm run dev"
echo "3. Run CORS tests: php artisan test --filter=CorsConfigurationTest"
echo "4. Monitor CORS logs: tail -f storage/logs/laravel.log | grep CORS"

echo -e "\n${BLUE}📚 For more testing, refer to the CORS specification:${NC}"
echo "app/backend/specs/docs/cors-configuration.md"