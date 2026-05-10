<?php

namespace App\Http\Controllers;

// ============================================================================
// HealthController — Application Health & Diagnostics
// ============================================================================
// Endpoints:
//   GET /up          — Simple liveness probe (used by Docker, load balancers)
//   GET /health      — Detailed readiness probe (DB, Redis, queue worker status)
//   GET /diagnostics — Full application diagnostics (admin only)
//
// WHY TWO ENDPOINTS:
//   /up       → Fast, no DB. If the PHP process is running, returns 200.
//                Used by load balancers to know if the pod is ALIVE.
//   /health   → Checks actual dependencies. Returns 503 if DB/Redis is down.
//                Used by monitoring to know if the pod is READY to serve traffic.
//   /diagnostics → Admin-only full audit of all system components.
// ============================================================================

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

class HealthController extends Controller
{
    /**
     * Liveness probe — extremely fast, no external calls.
     * If this 500s, the PHP process itself is broken.
     *
     * Route: GET /up
     */
    public function up(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'app'    => config('app.name'),
            'env'    => config('app.env'),
        ], 200);
    }

    /**
     * Readiness probe — checks all required dependencies.
     * Returns 503 if any critical component is unavailable.
     * The load balancer will stop routing traffic to this pod if 503.
     *
     * Route: GET /health
     */
    public function check(): JsonResponse
    {
        $checks  = [];
        $healthy = true;

        // ── Database ──────────────────────────────────────────────────────
        try {
            DB::selectOne('SELECT 1');
            $checks['database'] = ['status' => 'ok'];
        } catch (\Throwable $e) {
            $checks['database'] = ['status' => 'error', 'message' => 'Database unreachable'];
            $healthy = false;
        }

        // ── Redis ─────────────────────────────────────────────────────────
        try {
            Redis::ping();
            $checks['redis'] = ['status' => 'ok'];
        } catch (\Throwable $e) {
            $checks['redis'] = ['status' => 'error', 'message' => 'Redis unreachable'];
            $healthy = false;
        }

        // ── Cache ─────────────────────────────────────────────────────────
        try {
            $cacheKey = 'health_check_' . time();
            Cache::put($cacheKey, 'ok', 5);
            $result = Cache::get($cacheKey);
            $checks['cache'] = ['status' => $result === 'ok' ? 'ok' : 'error'];
        } catch (\Throwable $e) {
            $checks['cache'] = ['status' => 'error', 'message' => 'Cache write failed'];
            $healthy = false;
        }

        // ── Queue (check failed jobs threshold) ───────────────────────────
        try {
            $failedJobsCount = DB::table('failed_jobs')->count();
            $checks['queue'] = [
                'status'      => $failedJobsCount < 100 ? 'ok' : 'warning',
                'failed_jobs' => $failedJobsCount,
            ];
        } catch (\Throwable $e) {
            $checks['queue'] = ['status' => 'unknown'];
        }

        $statusCode = $healthy ? 200 : 503;

        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'checks' => $checks,
            'timestamp' => now()->toISOString(),
        ], $statusCode);
    }

    /**
     * Full diagnostics — admin only.
     * Shows detailed system metrics, version info, and component status.
     *
     * Route: GET /diagnostics
     * Middleware: auth, admin
     */
    public function diagnostics(): JsonResponse
    {
        $this->middleware('auth');
        $this->middleware('admin');

        try {
            $dbStats = DB::selectOne("
                SELECT
                    (SELECT COUNT(*) FROM users)        AS total_users,
                    (SELECT COUNT(*) FROM orders)       AS total_orders,
                    (SELECT COUNT(*) FROM transactions) AS total_transactions,
                    (SELECT COUNT(*) FROM failed_jobs)  AS failed_jobs
            ");
        } catch (\Throwable $e) {
            $dbStats = null;
        }

        return response()->json([
            'app' => [
                'name'     => config('app.name'),
                'env'      => config('app.env'),
                'debug'    => config('app.debug'),  // Should be false in production
                'timezone' => config('app.timezone'),
                'version'  => app()->version(),
            ],
            'php' => [
                'version'        => PHP_VERSION,
                'memory_limit'   => ini_get('memory_limit'),
                'max_exec_time'  => ini_get('max_execution_time'),
                'opcache_enabled'=> function_exists('opcache_get_status') && opcache_get_status(),
            ],
            'database' => [
                'connection' => config('database.default'),
                'stats'      => $dbStats,
            ],
            'queue' => [
                'driver'     => config('queue.default'),
                'failed_jobs' => DB::table('failed_jobs')->count(),
            ],
            'cache' => [
                'driver' => config('cache.default'),
            ],
            'timestamp' => now()->toISOString(),
        ]);
    }
}
