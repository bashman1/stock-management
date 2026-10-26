#!/bin/bash

PROJECT_DIR="/var/www/prodSmart"
BRANCH="bash"

echo "🚀 Starting deployment for $PROJECT_DIR on branch $BRANCH..."

cd "$PROJECT_DIR" || {
  echo "❌ Error: Directory $PROJECT_DIR does not exist."
  exit 1
}

# 1. BACKUP EXISTING .env
echo "💾 Backing up existing .env file..."
if [ -f .env ]; then
    cp .env .env.backup.$(date +%Y%m%d%H%M%S)
    echo "✅ Existing .env backed up"
else
    echo "⚠️ WARNING: No .env file found!"
fi

# 2. Change ownership for updates
echo "👤 Switching ownership to 'smart' for updates..."
sudo chown -R smart "$PROJECT_DIR"

# 3. Git operations
echo "🔄 Fetching latest changes from Git..."
sudo -u smart git fetch origin

echo "📌 Resetting local branch to origin/$BRANCH..."
sudo -u smart git reset --hard origin/$BRANCH || {
  echo "❌ Error: Git reset failed."
  exit 1
}

# 4. CRITICAL: Restore .env from backup (after git reset removes it)
echo "🔑 Restoring .env from backup..."
if [ -f .env.backup.* ]; then
    latest_backup=$(ls -t .env.backup.* | head -1)
    cp "$latest_backup" .env
    echo "✅ .env restored from $latest_backup"
else
    echo "❌ ERROR: No .env backup found! Please manually create .env file."
    exit 1
fi

# 5. Verify required EFRIS variables exist
echo "🔍 Verifying EFRIS configuration..."
if ! grep -q "EFRIS_BASE_URL" .env; then
    echo "❌ ERROR: EFRIS_BASE_URL not found in .env!"
    exit 1
fi
if ! grep -q "EFRIS_BEARER_TOKEN" .env; then
    echo "❌ ERROR: EFRIS_BEARER_TOKEN not found in .env!"
    exit 1
fi
echo "✅ EFRIS variables verified"

# 6. Composer install
echo "📦 Running composer install..."
sudo -u smart composer install --no-dev --optimize-autoloader || {
  echo "❌ Error: Composer install failed."
  exit 1
}

# 7. Run migrations
echo "🗄️ Running migrations..."
sudo -u smart php artisan migrate --force || {
  echo "❌ Error: Migration failed."
  exit 1
}

# 8. Clear ALL caches FIRST
echo "🧹 Clearing all caches first..."
sudo -u smart php artisan optimize:clear

# 9. Cache configuration (after .env is verified and caches cleared)
echo "⚙️ Caching configuration..."
sudo -u smart php artisan config:cache
sudo -u smart php artisan route:cache
sudo -u smart php artisan view:cache

# 10. Restart queue workers
echo "🔄 Restarting queue workers..."
sudo -u smart php artisan queue:restart

# 11. Change ownership back to www-data
echo "👥 Switching ownership back to 'www-data'..."
sudo chown -R www-data:www-data "$PROJECT_DIR"

echo "✅ Deployment completed successfully!"
