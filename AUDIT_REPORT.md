# SMM Panel — Full Production Audit Report

## CRITICAL VULNERABILITIES

### CRIT-01 · `.env` Committed With Real APP_KEY + Debug ON
- **File**: `.env`
- **Risk**: The live `APP_KEY` (`base64:AIh5x6v/PSv8X7...`) is checked into the repo. Anyone with repo access can decrypt all cookies, forge sessions, and impersonate any user including admins.
- **Also**: `APP_DEBUG=true` in production leaks full stack traces, file paths, and DB credentials in HTTP responses.
- **Fix**: Rotate APP_KEY immediately. Remove `.env` from git history with `git filter-repo`. Never commit real `.env`. See `.env.example` replacement below.

### CRIT-02 · SQLite In Production With Hardcoded Windows Path
- **File**: `.env` line `DB_DATABASE=C:\Users\kbros\Desktop\...`
- **Risk**: SQLite cannot handle concurrent writes — multiple queue workers or simultaneous requests will cause `SQLITE_BUSY` errors and lost transactions. The absolute Windows path fails on any Linux deployment.
- **Fix**: PostgreSQL with full connection pool config (see migration below).

### CRIT-03 · Queue Connection Is `sync`
- **File**: `.env` line `QUEUE_CONNECTION=sync`
- **Risk**: Every `ProcessOrderJob::dispatch()` runs **synchronously in the HTTP request**. Any provider timeout (up to 15 s) blocks the user's browser. Failed jobs are silently lost — no retry, no dead-letter queue.
- **Fix**: `QUEUE_CONNECTION=redis` with dedicated worker.

### CRIT-04 · PayPal Webhook Has Zero Verification
- **File**: `WebhookController.php` `paypal()` method
- **Risk**: Anyone can POST `{"event_type":"CHECKOUT.ORDER.APPROVED","resource":{"amount":{"total":"999"}}}` and trigger fund crediting with no verification whatsoever. Current code has a `// TODO` comment and no implementation.
- **Fix**: Full PayPal webhook verification via cert-chain validation (implemented below).

### CRIT-05 · Docker-Compose Uses MySQL, App Defaults To SQLite
- **File**: `docker-compose.yml` spins up MySQL, but `config/database.php` defaults to `sqlite`
- **Risk**: Docker environment connects to SQLite file, ignores MySQL entirely. All DB_HOST env vars are passed but never used by the app.
- **Fix**: Add `pgsql` connection block, set default to `pgsql`, remove SQLite dependency.

### CRIT-06 · Admin Balance Adjustment Has No Audit Trail
- **File**: `AdminController.php` `usersAddFunds()`
- **Risk**: Admin can add arbitrary funds to any user with no permanent record of who did it, when, or why. Enables insider fraud.
- **Fix**: Every balance mutation logged to `admin_action_logs` with admin ID, target user, old/new balance, IP, reason.

### CRIT-07 · Race Condition in Balance Deduction
- **File**: `OrderService.php` — uses `decrement()` after `lockForUpdate()` on a re-fetched user but the `find()` call is outside the lock scope
- **Risk**: Two simultaneous order submissions can both pass the balance check before either decrements.
- **Fix**: Wrap the entire balance check + decrement in a single `DB::transaction` with `lockForUpdate()` before reading balance.

---

## HIGH SEVERITY

### HIGH-01 · No PostgreSQL Connection Block
Config only has `sqlite` and `mysql`. PostgreSQL (recommended for production) is entirely absent.

### HIGH-02 · CSP Uses `unsafe-inline`
The Content-Security-Policy in `SecureHeaders.php` allows `unsafe-inline` scripts and styles, neutering XSS protection entirely.

### HIGH-03 · No Rate Limiting on Webhook Endpoints
Webhooks have no throttle middleware. An attacker can flood the endpoint with fake events to cause DB pressure.

### HIGH-04 · Referral Code Uses Weak MD5
`User::booted()` generates referral code with `md5($email . time())`. MD5 is not a CSPRNG; codes are predictable and can collide.

### HIGH-05 · No Email Verification
`RegisterController` creates active users without email verification. Enables account creation with others' emails.

### HIGH-06 · `QUEUE_CONNECTION=sync` Means No Retry on Provider Failure
`ProcessOrderJob` has `$tries = 5` with exponential backoff — all wasted because sync driver never retries.

### HIGH-07 · Stripe Webhook Missing Replay-Window Check
`WebhookController::stripe()` verifies signature but does not enforce Stripe's recommended 300-second tolerance window, risking replay of old valid events.

### HIGH-08 · No 2FA on Admin Accounts
Admin accounts are protected only by password. A compromised admin password = full system compromise.

### HIGH-09 · Funds Controller Missing Stripe/PayPal Action
`FundsController` has `stripe()` and `paypal()` methods referenced in routes but not in the current file — they are missing, causing 500 errors on fund addition attempts.

### HIGH-10 · Missing PaymentLog Model
`FundsController` imports `App\Models\PaymentLog` which doesn't exist in the codebase.

---

## MEDIUM SEVERITY

### MED-01 · No PostgreSQL in docker-compose
Docker-compose only has MySQL. Switch to PostgreSQL throughout.

### MED-02 · No CDN Nonce for CSP
Inline scripts need a nonce-based CSP for true XSS protection.

### MED-03 · Admin Dashboard Runs 8+ Separate COUNT Queries
`AdminController::dashboard()` fires 8 separate DB queries for stats. Should use a single aggregate query or Redis cache.

### MED-04 · N+1 in Provider Sync
`ProviderSyncService` loads all providers then fires N API calls sequentially. Should batch/queue.

### MED-05 · No Health Check for Queue Workers
No endpoint or mechanism detects dead queue workers.

### MED-06 · Logs Not JSON-Structured
Log entries are plaintext strings, making log aggregation (Datadog, Papertrail, CloudWatch) difficult.

### MED-07 · No Soft Deletes on Critical Models
Orders, transactions, and users have no soft deletes — accidental deletion is permanent.

### MED-08 · bootstrap/cache Committed to Repo
`bootstrap/cache/packages.php` and `services.php` are in the repo. These should be gitignored.

---

## PRODUCTION RISKS

### RISK-01 · No Zero-Downtime Deployment Strategy
`php artisan migrate` during deployment will lock tables and return 500 errors during the window.

### RISK-02 · No Backup Strategy Defined
No database backup configuration, schedule, or restore procedure.

### RISK-03 · Supervisor Config Not Included
Dockerfile references `docker/supervisor/supervisord.conf` which doesn't exist in the zip.

### RISK-04 · No Nginx Config
Dockerfile references `docker/nginx/default.conf` which doesn't exist in the zip.

### RISK-05 · `composer.phar` (3.3MB) Committed to Repo
Binary files should never be in git. Bloats repo and causes CI slowness.

### RISK-06 · `database/database.sqlite` (20MB) Committed to Repo
The SQLite development database with real-looking data is in the repo. Potential data leak.
