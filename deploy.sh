#!/bin/bash

# CRM Backend Deployment Script for Hostinger
# Usage: ./deploy.sh

echo "🚀 Starting CRM Backend Deployment..."

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Step 1: Check if .env exists
if [ ! -f .env ]; then
    echo -e "${RED}❌ Error: .env file not found${NC}"
    echo "Please copy .env.example to .env and configure it first"
    exit 1
fi

echo -e "${GREEN}✓${NC} Environment file found"

# Step 2: Install dependencies
echo -e "${YELLOW}📦 Installing dependencies...${NC}"
composer install --optimize-autoloader --no-dev

# Step 3: Generate app key if not set
if grep -q "APP_KEY=$" .env; then
    echo -e "${YELLOW}🔑 Generating application key...${NC}"
    php artisan key:generate
fi

# Step 4: Clear all caches
echo -e "${YELLOW}🧹 Clearing caches...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Step 5: Run migrations
echo -e "${YELLOW}📊 Running database migrations...${NC}"
php artisan migrate --force

# Step 6: Link storage
echo -e "${YELLOW}🔗 Linking storage...${NC}"
php artisan storage:link

# Step 7: Optimize for production
echo -e "${YELLOW}⚡ Optimizing for production...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Step 8: Set permissions
echo -e "${YELLOW}🔐 Setting permissions...${NC}"
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

echo ""
echo -e "${GREEN}✅ Deployment Complete!${NC}"
echo ""
echo "📋 Next Steps:"
echo "1. Update your mobile app's API URL to: https://yourdomain.com/api/v1"
echo "2. Test API: curl https://yourdomain.com/api/health"
echo "3. Access admin panel: https://yourdomain.com/admin"
echo "4. Monitor logs: tail -f storage/logs/laravel.log"
echo ""
echo "🎉 Your CRM backend is ready!"

