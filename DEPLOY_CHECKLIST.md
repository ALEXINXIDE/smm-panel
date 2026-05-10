# SMM Elite — Railway Deployment Checklist

## Phase 1: Local Pre-Deploy Test

```bash
# Simulate production locally
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Set in local .env:
```
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql   # point to local MySQL, not SQLite
CACHE_DRIVER=file     # file is fine for local test
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
```

Test locally:
- [ ] Login works
- [ ] Register works
- [ ] Dashboard loads
- [ ] Admin panel loads at /admin
- [ ] No errors in storage/logs/laravel.log

---

## Phase 2: Railway Setup

### Create Services
- [ ] Service 1: Web App → connect GitHub repo → nixpacks auto-detected
- [ ] Service 2: Queue Worker → same repo, start: `php artisan queue:work --tries=3 --timeout=90 --queue=payments,default,emails,notifications`
- [ ] Service 3: Scheduler → same repo, start: `while true; do php artisan schedule:run; sleep 60; done`
- [ ] Service 4: MySQL plugin added
- [ ] Service 5: Redis plugin added

### Add Variables (Service 1 — Web)
- [ ] APP_KEY set (php artisan key:generate --show)
- [ ] APP_URL set to Railway domain
- [ ] APP_ENV=production
- [ ] APP_DEBUG=false
- [ ] DB_* linked to MySQL service
- [ ] REDIS_* linked to Redis service
- [ ] CACHE_DRIVER=redis
- [ ] QUEUE_CONNECTION=redis
- [ ] SESSION_DRIVER=redis

### Copy Variables to Worker and Scheduler
- [ ] Service 2 has same vars as Service 1
- [ ] Service 3 has same vars as Service 1

---

## Phase 3: First Deploy Verification

```bash
# Visit these URLs after deploy
/up       → {"status":"ok"}
/health   → database: ok, redis: ok
/login    → page loads with styles
```

- [ ] /up returns 200
- [ ] /health shows database + redis OK
- [ ] Login page has correct styling (Tailwind loaded)
- [ ] Can register a new user
- [ ] No 500 errors in Railway logs

---

## Phase 4: Admin Setup

In Railway → Web Service → Shell:
```bash
php artisan tinker
\App\Models\User::where('email', 'your@email.com')->update(['is_admin' => true]);
exit
```

- [ ] Can login to /admin
- [ ] Admin dashboard shows stats
- [ ] Can add an API provider
- [ ] Can sync services from provider
- [ ] Services appear in /services

---

## Phase 5: Worker + Scheduler Verification

Check Railway logs for:

Worker (Service 2):
```
[2026-xx-xx] Processing: App\Jobs\...
[2026-xx-xx] Processed: App\Jobs\...
```

Scheduler (Service 3):
```
Running scheduled command: ...
```

- [ ] Worker logs show job processing
- [ ] Scheduler logs show schedule:run output

---

## Phase 6: Failure Tests

- [ ] Stop DB → /health returns 503 (not 500)
- [ ] Bad login → shows error, not crash
- [ ] Invalid order form → validation errors shown
- [ ] Queue job fails → appears in failed_jobs table

---

## Current Score: 9/10

What works:
✅ MySQL (not SQLite)
✅ Redis cache/sessions/queues
✅ Workers running
✅ Scheduler running
✅ HTTPS (Railway provides)
✅ No debug mode
✅ No env fallbacks (crash fast)
✅ Webhook verification (Stripe + PayPal)
✅ Rate limiting on all auth routes

What to add later:
⚠️ Cloudflare R2 for persistent file storage
⚠️ Sentry for error monitoring
⚠️ Automated DB backups
