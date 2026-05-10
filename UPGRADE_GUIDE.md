# SMM Panel — Production Upgrade Guide

## Overview

This guide explains every change made to transform the project from a local
development build into a production-grade SaaS system. Changes are ordered by
severity and grouped by category.

---

## PHASE 1 — IMMEDIATE ACTIONS (Deployment Blockers)

### 1.1 — Rotate APP_KEY and Remove .env from Git

**Issue**: The `.env` file with `APP_KEY=base64:AIh5x6v/PSv8X7/htqyo+jn1T7ygPI3w85OHphxxuLA=`
is committed to the repository. Anyone with repo access can:
- Decrypt all encrypted cookies
- Forge signed URLs
- Impersonate any user including admins

**Action**:
```bash
# 1. Remove .env from git (current and history)
git rm --cached .env
git rm --cached database/database.sqlite
git rm --cached composer.phar

# 2. Add to .gitignore (already done in output/.gitignore)
echo ".env" >> .gitignore
echo "*.sqlite" >> .gitignore
echo "composer.phar" >> .gitignore

# 3. Remove from ALL git history (requires git-filter-repo)
pip install git-filter-repo
git filter-repo --path .env --invert-paths
git filter-repo --path database/database.sqlite --invert-paths

# 4. Generate new APP_KEY on production server
php artisan key:generate

# 5. Force push (coordinate with team)
git push --force-with-lease
```

### 1.2 — Switch to PostgreSQL

**Replace** `config/database.php` with `output/config/database.php`.
**Run** the new comprehensive migration `output/database/migrations/2024_01_01_000010_...php`.

**In .env**:
```
DB_CONNECTION=pgsql
DB_HOST=your-postgres-host
DB_PORT=5432
DB_DATABASE=smm_panel
DB_USERNAME=smm_user
DB_PASSWORD=your-strong-password
DB_SSLMODE=require
```

### 1.3 — Enable Redis Queue

**In .env**:
```
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=your-redis-host
REDIS_PASSWORD=your-redis-password
```

### 1.4 — Set Payment Webhook Secrets

```
STRIPE_WEBHOOK_SECRET=whsec_...   # From Stripe Dashboard → Webhooks
PAYPAL_WEBHOOK_ID=WH-...          # From PayPal Developer → Webhooks
```

---

## PHASE 2 — FILE REPLACEMENTS

Replace these files with the versions in `output/`:

| Original File | Replacement | Why |
|---|---|---|
| `.env` | `.env.example` (template only) | Remove real credentials |
| `.gitignore` | `output/.gitignore` | Block sensitive files |
| `config/database.php` | `output/config/database.php` | Add PostgreSQL |
| `app/Http/Controllers/WebhookController.php` | `output/app/Http/Controllers/WebhookController.php` | PayPal verification |
| `app/Jobs/ProcessWebhookPaymentJob.php` | (NEW) | Queue-based payment processing |
| `app/Services/OrderService.php` | `output/app/Services/OrderService.php` | Race condition fix |
| `app/Http/Controllers/Admin/AdminController.php` | `output/app/Http/Controllers/Admin/AdminController.php` | Audit logging |
| `app/Http/Controllers/FundsController.php` | `output/app/Http/Controllers/FundsController.php` | Stripe PaymentIntent flow |
| `app/Http/Middleware/SecureHeaders.php` | `output/app/Http/Middleware/SecureHeaders.php` | CSP nonce |
| `app/Http/Middleware/VerifyCsrfToken.php` | `output/app/Http/Middleware/VerifyCsrfToken.php` | Webhook exclusions |
| `app/Models/User.php` | `output/app/Models/User.php` | CSPRNG referral codes, login locking |
| `app/Providers/AppServiceProvider.php` | `output/app/Providers/AppServiceProvider.php` | Force HTTPS, N+1 detection |
| `app/Console/Kernel.php` | `output/app/Console/Kernel.php` | Full scheduler |
| `app/Exceptions/Handler.php` | `output/app/Exceptions/Handler.php` | Sentry, structured errors |
| `docker-compose.yml` | `output/docker-compose.yml` | PostgreSQL, worker queues |
| `Dockerfile` | `output/Dockerfile` | pdo_pgsql extension |

**New files to add**:
- `output/app/Models/PaymentLog.php` → `app/Models/PaymentLog.php`
- `output/app/Exceptions/DomainExceptions.php` → `app/Exceptions/DomainExceptions.php`
- `output/app/Jobs/ProcessWebhookPaymentJob.php` → `app/Jobs/ProcessWebhookPaymentJob.php`
- `output/app/Console/Commands/PruneProviderLogs.php` → `app/Console/Commands/PruneProviderLogs.php`
- `output/app/Console/Commands/QueueHealthCheck.php` → `app/Console/Commands/QueueHealthCheck.php`
- `output/app/Console/Commands/ValidateEnvironment.php` → `app/Console/Commands/ValidateEnvironment.php`
- `output/app/Http/Controllers/HealthController.php` → `app/Http/Controllers/HealthController.php`
- `output/docker/nginx/default.conf` → `docker/nginx/default.conf`
- `output/docker/supervisor/supervisord.conf` → `docker/supervisor/supervisord.conf`
- `output/docker/php/php.ini` → `docker/php/php.ini`
- `output/docker/php/www.conf` → `docker/php/www.conf`
- `output/docker/entrypoint.sh` → `docker/entrypoint.sh`
- `output/docker/postgres/postgresql.conf` → `docker/postgres/postgresql.conf`
- `output/.github/workflows/ci.yml` → `.github/workflows/ci.yml`
- `output/routes/webhooks.php` → include from `routes/web.php`

---

## PHASE 3 — ROUTES UPDATE

Add to the bottom of `routes/web.php`:
```php
// Include webhook routes
require __DIR__ . '/webhooks.php';
```

Or merge the webhook and health routes from `output/routes/webhooks.php` directly
into your `routes/web.php`.

---

## PHASE 4 — COMPOSER PACKAGES

Add these packages:
```bash
# Stripe PHP SDK (likely already installed)
composer require stripe/stripe-php

# Sentry error tracking
composer require sentry/sentry-laravel

# GuzzleHTTP (for PayPal API calls — likely already installed)
composer require guzzlehttp/guzzle
```

After adding Sentry, publish config:
```bash
php artisan vendor:publish --provider="Sentry\Laravel\ServiceProvider"
```

---

## PHASE 5 — DEPLOYMENT SEQUENCE

```bash
# 1. Validate environment before deploying
php artisan env:validate

# 2. Run migrations
php artisan migrate --force --isolated

# 3. Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Verify health
curl https://yourdomain.com/health
# Expected: {"status":"healthy","checks":{"database":{"status":"ok"},"redis":{"status":"ok"},...}}

# 5. Run tests
vendor/bin/phpunit
```

---

## SECURITY NOTES

### What Was Fixed

| Vulnerability | Severity | Status |
|---|---|---|
| APP_KEY in git | CRITICAL | ✅ Fixed — rotate immediately |
| .env in git | CRITICAL | ✅ Fixed — add to .gitignore |
| SQLite in production | CRITICAL | ✅ Fixed — PostgreSQL |
| QUEUE_CONNECTION=sync | CRITICAL | ✅ Fixed — Redis |
| PayPal webhook unverified | CRITICAL | ✅ Fixed — cert-chain verification |
| Race condition in balance | HIGH | ✅ Fixed — lockForUpdate() |
| No admin audit trail | HIGH | ✅ Fixed — admin_action_logs |
| CSP unsafe-inline | HIGH | ✅ Fixed — nonce-based CSP |
| No idempotency on webhooks | HIGH | ✅ Fixed — idempotency_key |
| PaymentLog model missing | HIGH | ✅ Fixed — model + migration created |
| Weak referral code (MD5) | MEDIUM | ✅ Fixed — CSPRNG |
| No email verification | MEDIUM | Architecture ready (enable in config) |
| bootstrap/cache in git | MEDIUM | ✅ Fixed — gitignore |
| database.sqlite in git | MEDIUM | ✅ Fixed — gitignore + delete |

### What Still Needs Manual Setup

- **2FA**: Architecture is in place (DB columns, model helpers) but UI not built
- **Email Verification**: Set `'verify' => true` in `config/auth.php` guards
- **Admin IP Whitelist**: Add middleware checking `ADMIN_IP_WHITELIST` env var
- **Database Backup**: Uncomment backup scheduler in `Kernel.php` after installing `spatie/laravel-backup`
- **Sentry Alerting**: Configure alert rules in Sentry dashboard after adding DSN

---

## SCALABILITY NOTES

The architecture now supports horizontal scaling:

- **Multiple app containers**: Sessions in Redis (shared state)
- **Multiple queue workers**: Each worker independently reads from Redis queue
- **Cache coherence**: All instances share Redis cache
- **Zero-downtime deploys**: `migrate --isolated` prevents concurrent migrations
- **Load balancer ready**: `/health` returns 503 when dependencies are down

To scale under load:
1. Add more `worker-payments` replicas in docker-compose
2. Put Nginx behind a CDN (Cloudflare) for static assets
3. Add PostgreSQL connection pooler (PgBouncer) at 500+ concurrent users
4. Consider read replica for analytics queries at 1000+ users
