# SMM Elite Panel — Complete Integration Guide

## What's in this package

| File | Location in Laravel | Purpose |
|---|---|---|
| `layouts/app.blade.php` | `resources/views/layouts/` | Master layout — Stitch design system |
| `dashboard/index.blade.php` | `resources/views/dashboard/` | User dashboard with real metrics |
| `orders/create.blade.php` | `resources/views/orders/` | 4-step order wizard |
| `orders/index.blade.php` | `resources/views/orders/` | Orders list with live filter |
| `orders/show.blade.php` | `resources/views/orders/` | Single order detail + progress |
| `funds/index.blade.php` | `resources/views/funds/` | Add funds with fee calculator |
| `services/index.blade.php` | `resources/views/services/` | Services table with PKR + live search |
| `referrals/index.blade.php` | `resources/views/referrals/` | Referral program dashboard |
| `support/index.blade.php` | `resources/views/support/` | Tickets create + reply |
| `analytics/index.blade.php` | `resources/views/analytics/` | Charts + top services |
| `admin/dashboard.blade.php` | `resources/views/admin/` | Admin command center |
| `auth/login.blade.php` | `resources/views/auth/` | Login page |
| `auth/register.blade.php` | `resources/views/auth/` | Register + referral support |
| `landing.blade.php` | `resources/views/` | Public landing page |
| `DashboardController.php` | `app/Http/Controllers/` | Dashboard data |
| `MainControllers.php` | `app/Http/Controllers/` | OrderController, ServiceController, FundsController |
| `UserControllers.php` | `app/Http/Controllers/` | AnalyticsController, ReferralController, TicketController, TransactionController |
| `Admin/AdminController.php` | `app/Http/Controllers/Admin/` | Full admin panel logic |
| `Auth/RegisterController.php` | `app/Http/Controllers/Auth/` | Register with referral |
| `Models/User.php` | `app/Models/` | User model with referral |
| `Models/Order.php` | `app/Models/` | Order model |
| `Models/Models.php` | `app/Models/` | Service, Category, ApiProvider, Transaction, Ticket, TicketMessage |
| `Services/Services.php` | `app/Services/` | ProviderApiService + ExchangeRateService |
| `Console/Commands/SyncOrderStatus.php` | `app/Console/Commands/` | Auto-sync orders every 5 min |
| `Console/Kernel.php` | `app/Console/` | Scheduler |
| `Http/Middleware/InjectExchangeRate.php` | `app/Http/Middleware/` | Live PKR rate injection |
| `routes/web.php` | `routes/` | All routes |
| `migrations/` | `database/migrations/` | All DB tables |
| `public/manifest.json` | `public/` | PWA install support |

---

## Step 1 — Create fresh Laravel 10 project

```bash
# On Termux (Android) or your server:
composer create-project laravel/laravel smm-elite
cd smm-elite
```

---

## Step 2 — Copy all files

Copy every file from this zip into the correct location shown in the table above.

For the models in `Models.php` — split each class into its own file:
- `Models/Models.php` → split into `Service.php`, `Category.php`, `ApiProvider.php`, `Transaction.php`, `Ticket.php`, `TicketMessage.php`

For the controllers in `MainControllers.php` — split each class:
- `OrderController` → `OrderController.php`
- `ServiceController` → `ServiceController.php`
- `FundsController` → `FundsController.php`

For `UserControllers.php` — split each class:
- `AnalyticsController` → `AnalyticsController.php`
- `ReferralController` → `ReferralController.php`
- `TicketController` → `TicketController.php`
- `TransactionController` → `TransactionController.php`

---

## Step 3 — Configure .env

```env
APP_NAME="SMM Elite"
APP_URL=http://localhost:8000
APP_KEY=   # run: php artisan key:generate

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smmelite
DB_USERNAME=root
DB_PASSWORD=

# ─────────────────────────────────────────────
# PAYMENT GATEWAY API KEYS — ADD YOURS HERE
# ─────────────────────────────────────────────

# Stripe (credit/debit cards)
STRIPE_KEY=pk_live_xxxxxxxxxxxxxxxxxxxx
STRIPE_SECRET=sk_live_xxxxxxxxxxxxxxxxxxxx
# Get from: https://dashboard.stripe.com/apikeys

# PayPal
PAYPAL_CLIENT_ID=xxxxxxxxxxxxxxxxxxxxxxxx
PAYPAL_SECRET=xxxxxxxxxxxxxxxxxxxxxxxx
PAYPAL_MODE=live   # or 'sandbox' for testing
# Get from: https://developer.paypal.com/dashboard

# ─────────────────────────────────────────────
# EMAIL — use Brevo free tier (300/day)
# ─────────────────────────────────────────────
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your_brevo_smtp_key
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="SMM Elite"
# Get from: https://app.brevo.com/settings/keys/smtp

# Cache (use Upstash Redis free tier for speed)
# CACHE_DRIVER=redis
# REDIS_URL=redis://default:password@host:6379
```

---

## Step 4 — WHERE TO ADD YOUR SMM PROVIDER API KEYS

**This is the most important step** — without this, no services will appear.

1. Run migrations and start the server
2. Register an account at `/register`
3. Manually set your account as admin in the database:
```sql
UPDATE users SET is_admin = 1 WHERE email = 'your@email.com';
```
4. Go to `/admin/providers/create`
5. Fill in:

| Field | Value |
|---|---|
| Name | Peakerr (or SMMWorld, SMMRaja, etc.) |
| API URL | `https://peakerr.com/api/v2` |
| API Key | Your key from the provider dashboard |
| Markup % | `200` means you charge 3x supplier price |

6. Click **Save**, then click **Sync** on the provider
7. All services import automatically — they appear in `/services`

**Free provider to test with (no money needed):**
- Register at peakerr.com or smmworld.org
- Get your API key from Account → API
- Set markup to 200% to start

---

## Step 5 — Run migrations

```bash
php artisan migrate
php artisan key:generate
```

---

## Step 6 — Register Kernel middleware

Open `app/Http/Kernel.php` and add to the `web` middleware group:

```php
protected $middlewareGroups = [
    'web' => [
        // ... existing lines ...
        \App\Http\Middleware\InjectExchangeRate::class,  // ADD THIS
    ],
];
```

---

## Step 7 — Start (Termux / local)

```bash
# Terminal 1 — start database
mysqld_safe -u root &

# Terminal 2 — start Laravel
cd smm-elite
php artisan migrate
php artisan serve --host=0.0.0.0 --port=8000
```

Open Chrome: `http://localhost:8000`

---

## Step 8 — Deploy free on Railway

```bash
# Add to project root:
echo "web: php artisan serve --host=0.0.0.0 --port=\$PORT" > Procfile
```

Create `nixpacks.toml` in root:
```toml
[phases.setup]
nixPkgs = ["php82","php82Extensions.pdo_mysql","php82Extensions.gd","php82Extensions.mbstring","php82Extensions.xml","php82Extensions.curl","composer"]

[phases.install]
cmds = ["composer install --no-dev --optimize-autoloader"]

[phases.build]
cmds = ["php artisan key:generate --force","php artisan config:cache","php artisan route:cache","php artisan view:cache"]

[start]
cmd = "php artisan serve --host=0.0.0.0 --port=$PORT"
```

Then:
1. Push to GitHub
2. Go to railway.app → New Project → Deploy from GitHub
3. Add all `.env` variables in Railway dashboard
4. Add cron service: `php artisan schedule:run` every `* * * * *`
5. Connect PlanetScale for free MySQL database

---

## Step 9 — Set up cron for auto order sync

```bash
# On server / Railway cron service:
* * * * * php artisan schedule:run >> /dev/null 2>&1
```

This runs `orders:sync` every 5 minutes — keeps all order statuses updated automatically.

---

## Bugs fixed vs Manus original

| Bug | Status |
|---|---|
| Hardcoded PKR × 278 | ✅ Fixed — live API, session-injected |
| `Auth::user()->balance` wrong field | ✅ Fixed — uses `->funds` |
| Hardcoded service rate $5.00 in calculator | ✅ Fixed — reads real DB rate |
| Empty Chart.js canvas tags | ✅ Fixed — fully initialized |
| Dark mode toggle not working | ✅ Fixed — JS toggle + localStorage |
| Progress bars hardcoded 30%/40%/70% | ✅ Fixed — real % from DB counts |
| No Tailwind pipeline in webpack.mix.js | ✅ Fixed — CDN Tailwind in all views |
| No auto order sync scheduler | ✅ Fixed — SyncOrderStatus command |
| Mass assignment vulnerability on User | ✅ Fixed — explicit `$fillable` |
| Balance race condition on orders | ✅ Fixed — DB transaction + lockForUpdate |
| Unauthenticated public API endpoint | ✅ Fixed — auth middleware on routes |
| No CSRF on API provider forms | ✅ Fixed — all forms have @csrf |

---

## Enhancement roadmap (future)

| Priority | Feature | Effort |
|---|---|---|
| High | Stripe + PayPal live integration | 2 hours |
| High | EasyPaisa/JazzCash manual verification flow | 1 hour |
| High | Telegram bot ordering (/balance, /order, /status) | 4 hours |
| Medium | WhatsApp order notifications via Twilio | 2 hours |
| Medium | Subscription auto-refill plans | 6 hours |
| Medium | Multi-supplier smart routing with failover | 4 hours |
| Low | Urdu language toggle | 3 hours |
| Low | Reseller child panel system | 8 hours |
