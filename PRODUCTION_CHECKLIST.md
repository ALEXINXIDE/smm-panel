# SMM Panel — Production Readiness Checklist

## HOW TO USE THIS CHECKLIST
Run through every item before going live. Each `[ ]` must become `[x]`.
Items marked 🚨 are **deployment blockers** — the app must not go live with these unresolved.

---

## ☑ SECURITY CHECKLIST

### Environment
- [ ] 🚨 `.env` removed from git history (`git filter-repo --path .env --invert-paths`)
- [ ] 🚨 New `APP_KEY` generated (`php artisan key:generate`)
- [ ] 🚨 `APP_DEBUG=false` in production `.env`
- [ ] 🚨 `APP_ENV=production` in production `.env`
- [ ] `.env.example` committed (no real values, all keys documented)
- [ ] `.gitignore` updated (blocks `.env`, `*.sqlite`, `composer.phar`)

### Authentication
- [ ] 🚨 Login throttle active (`throttle:10,1` on POST /login)
- [ ] 🚨 Admin routes double-protected (`auth` + `admin` middleware)
- [ ] `is_admin` NOT in User `$fillable`
- [ ] `funds` NOT in User `$fillable`
- [ ] `status` NOT in User `$fillable`
- [ ] Password minimum complexity enforced in RegisterController
- [ ] Remember token uses secure random bytes (Laravel default)

### Headers & Transport
- [ ] `SecureHeaders` middleware active in web middleware group
- [ ] HSTS enabled in production (`Strict-Transport-Security`)
- [ ] CSP uses nonce (not `unsafe-inline`)
- [ ] `X-Frame-Options: SAMEORIGIN` set
- [ ] `X-Content-Type-Options: nosniff` set
- [ ] `Permissions-Policy` header restricts camera/mic/geolocation

### Payment Security
- [ ] 🚨 `STRIPE_WEBHOOK_SECRET` set in production `.env`
- [ ] 🚨 `PAYPAL_WEBHOOK_ID` set in production `.env`
- [ ] 🚨 Stripe webhook signature verified in WebhookController
- [ ] 🚨 PayPal webhook signature verified via PayPal verify API
- [ ] 🚨 PayPal cert URL validated against PayPal domains
- [ ] Idempotency key prevents double-credit on webhook retry
- [ ] Unique constraint on `transactions.reference` (DB-level safety net)
- [ ] `lockForUpdate()` used on User row before balance mutations
- [ ] All balance changes wrapped in `DB::transaction()`
- [ ] `payment_logs` table records every payment event

### Admin Security
- [ ] Every admin balance mutation logged to `admin_action_logs`
- [ ] Reason required for fund adjustments
- [ ] Admin cannot ban other admin accounts
- [ ] Admin sessions invalidated when user is banned

---

## ☑ DATABASE CHECKLIST

- [ ] 🚨 `DB_CONNECTION=pgsql` in production `.env`
- [ ] 🚨 No SQLite files committed to repository
- [ ] 🚨 `database.sqlite` deleted from repository history
- [ ] PostgreSQL connection configured with SSL (`DB_SSLMODE=require`)
- [ ] All migrations run (`php artisan migrate --force`)
- [ ] Indexes applied (migration `000010` run)
- [ ] Foreign key constraints verified
- [ ] `transactions.reference` unique constraint active
- [ ] Soft deletes on users, orders, transactions

---

## ☑ QUEUE CHECKLIST

- [ ] 🚨 `QUEUE_CONNECTION=redis` in production `.env`
- [ ] 🚨 Queue workers running (supervisor or Docker worker container)
- [ ] `payments` queue has dedicated workers (2+ recommended)
- [ ] `default` queue has workers
- [ ] Failed jobs table exists (`failed_jobs`)
- [ ] Job retry counts configured (ProcessOrderJob: 5 tries)
- [ ] Exponential backoff configured on all jobs
- [ ] `queue:health-check` scheduled (every 15 min)
- [ ] Alert fires when `failed_jobs` > 50

---

## ☑ DEPLOYMENT CHECKLIST

### Pre-Deploy
- [ ] `php artisan env:validate` passes with no errors
- [ ] All tests pass (`vendor/bin/phpunit`)
- [ ] `composer audit` shows no high/critical vulnerabilities
- [ ] Docker image builds successfully
- [ ] `.env` in deployment environment (not in image)

### Deploy Sequence
```bash
# 1. Build image
docker build -t smm-panel:latest .

# 2. Run migrations (isolated prevents concurrent migration runs)
php artisan migrate --force --isolated

# 3. Cache config/routes/views
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Start workers
# (handled by supervisor/docker-compose)

# 5. Verify health
curl https://yourdomain.com/health
```

### Post-Deploy Verification
- [ ] `/up` returns 200
- [ ] `/health` returns `{"status":"healthy"}`
- [ ] Admin dashboard loads without errors
- [ ] Test order can be placed (use test service)
- [ ] Stripe test webhook verified in Stripe dashboard
- [ ] Queue workers showing in Horizon or process list

---

## ☑ PERFORMANCE CHECKLIST

- [ ] OPcache enabled (`opcache.enable=1` in php.ini)
- [ ] `opcache.validate_timestamps=0` in production (set to 1 in dev)
- [ ] `php artisan config:cache` run after deployment
- [ ] `php artisan route:cache` run after deployment
- [ ] `php artisan view:cache` run after deployment
- [ ] Redis caching active for admin dashboard stats
- [ ] Eager loading on all paginated list views (no N+1)
- [ ] Database indexes verified (`\d orders` in psql)
- [ ] Nginx gzip compression enabled
- [ ] Static assets have long-lived cache headers

---

## ☑ MONITORING CHECKLIST

- [ ] `SENTRY_LARAVEL_DSN` set in `.env`
- [ ] `/health` endpoint monitored by uptime service (Better Uptime, UptimeRobot)
- [ ] `LOG_SLACK_WEBHOOK_URL` set for critical error alerts
- [ ] Failed job alerts configured (`queue:health-check`)
- [ ] Provider sync logs reviewed (`/admin/logs/providers`)
- [ ] Payment logs accessible (`/admin/logs/payments`)
- [ ] Admin action audit trail accessible (`/admin/logs/activity`)

---

## ☑ BACKUP CHECKLIST

- [ ] Daily PostgreSQL backup configured
- [ ] Backups stored off-host (S3, Backblaze, etc.)
- [ ] Backup restoration tested (restore to staging, verify app works)
- [ ] Redis persistence enabled (`--appendonly yes`)
- [ ] Backup retention policy defined (7 daily, 4 weekly, 3 monthly)
- [ ] Backup alert if backup fails

---

## ☑ INCIDENT RECOVERY CHECKLIST

### If DB goes down
1. `/health` returns `{"status":"unhealthy"}` — load balancer stops traffic
2. Queue workers pause (jobs stay in Redis queue)
3. Restore from latest backup to new PostgreSQL instance
4. Update `DB_HOST` in `.env`
5. Restart app containers
6. Verify `/health` returns healthy
7. Confirm no payments lost (check `payment_logs` vs Stripe dashboard)

### If Redis goes down
1. Cache driver falls back to DB cache (performance degrades but app works)
2. Queue jobs are lost if Redis has no persistence — enable AOF
3. Sessions are lost — users must re-login
4. Fix: restart Redis, confirm AOF replay worked

### If Queue Workers die
1. Check supervisor/docker logs
2. Jobs stay in Redis queue (not lost)
3. Restart workers: `docker-compose restart worker-payments worker-default`
4. Check `failed_jobs` for any jobs that exceeded retry limit

### If Payment Webhook Fails
1. Stripe retries webhooks for 72 hours automatically
2. Check `/admin/logs/payments` for failed entries
3. Check Stripe dashboard → Webhooks → Events for delivery status
4. Jobs in `failed_jobs` table can be retried: `php artisan queue:retry all`
5. If double-credit is suspected: check `transactions` for duplicate `reference`

---

## ☑ LONG-TERM MAINTENANCE CHECKLIST

- [ ] `composer outdated` reviewed monthly
- [ ] `composer audit` run weekly (automated in CI)
- [ ] Laravel version upgrade path planned (10 → 11)
- [ ] PHP 8.2 EOL tracked (active support until Dec 2025)
- [ ] Provider API keys rotated quarterly
- [ ] Admin accounts reviewed quarterly
- [ ] `provider_logs` pruned (automated via scheduler)
- [ ] `activity_logs` pruned (automated via scheduler)
- [ ] Failed jobs reviewed weekly
- [ ] Slow query log reviewed monthly (`log_min_duration_statement=1000ms`)
