#!/bin/bash
# ==============================================================================
# docker/entrypoint.sh — Container Startup Script
# ==============================================================================
# Runs before the main process (supervisord) starts.
# Handles:
#  1. Wait for database to be available before running migrations
#  2. Run pending migrations (with --force for production)
#  3. Cache config, routes, views for performance
#  4. Run composer post-install scripts (storage:link, etc.)
# ==============================================================================

set -e

echo "=== SMM Panel: Container starting ==="

# ── Wait for Database ──────────────────────────────────────────────────────────
echo "==> Waiting for database..."
MAX_TRIES=30
TRIES=0

until php -r "new PDO('pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE', '$DB_USERNAME', '$DB_PASSWORD');" >/dev/null 2>&1; do
    TRIES=$((TRIES + 1))
    if [ $TRIES -ge $MAX_TRIES ]; then
        echo "ERROR: Database not available after $MAX_TRIES attempts. Exiting."
        exit 1
    fi
    echo "    Database not ready (attempt $TRIES/$MAX_TRIES)..."
    sleep 2
done

echo "    Database is ready!"

# ── Run Migrations ─────────────────────────────────────────────────────────────
# --force: required for production (non-interactive)
# --isolated: uses atomic lock to prevent multiple containers running migrations simultaneously
echo "==> Running database migrations..."
php artisan migrate --force --isolated

# ── Clear + Rebuild Production Caches ─────────────────────────────────────────
# This must run AFTER migrations because config may reference DB tables
echo "==> Caching configuration..."
php artisan config:cache

echo "==> Caching routes..."
php artisan route:cache

echo "==> Caching views..."
# Run view:clear explicitly first so any permission/path errors surface clearly
# (view:cache calls view:clear internally via callSilent, which swallows errors)
php artisan view:clear
php artisan view:cache

# ── Storage Link ───────────────────────────────────────────────────────────────
# Creates public/storage -> storage/app/public symlink
# --force: replace existing (safe to run repeatedly)
echo "==> Creating storage symlink..."
php artisan storage:link --force 2>/dev/null || true

# ── Queue Clear (optional — clears stale pending jobs on redeploy) ─────────────
# Uncomment only if you want to clear the queue on every deploy
# WARNING: This will lose any queued jobs that haven't run yet
# php artisan queue:clear redis

echo "=== Startup complete — launching supervisord ==="

exec "$@"
