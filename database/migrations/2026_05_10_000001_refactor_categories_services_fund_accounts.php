<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Refactor Migration — SMM Panel
 * ─────────────────────────────────────────────────────────────────────────────
 * 1. categories   → add `platform` + `type` columns
 * 2. services     → add composite indexes (price ASC, category_id) for filtering
 * 3. fund_accounts → add `is_active` boolean (mirrors PaymentAccount pattern)
 * ─────────────────────────────────────────────────────────────────────────────
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. CATEGORIES ────────────────────────────────────────────────────
        Schema::table('categories', function (Blueprint $table) {
            // Platform the category belongs to (instagram, tiktok, youtube…)
            $table->string('platform', 50)->nullable()->after('name');

            // Service type within a platform (followers, likes, views…)
            $table->string('type', 50)->nullable()->after('platform');

            // Composite index — used by ServiceController filter queries
            $table->index(['platform', 'type', 'status'], 'categories_platform_type_status_idx');
        });

        // Seed canonical platform+type values from existing category names.
        $map = [
            '%instagram%follower%' => ['instagram', 'followers'],
            '%instagram%like%'     => ['instagram', 'likes'],
            '%instagram%view%'     => ['instagram', 'views'],
            '%tiktok%follower%'    => ['tiktok',    'followers'],
            '%tiktok%like%'        => ['tiktok',    'likes'],
            '%tiktok%view%'        => ['tiktok',    'views'],
            '%youtube%view%'       => ['youtube',   'views'],
            '%youtube%like%'       => ['youtube',   'likes'],
            '%youtube%subscriber%' => ['youtube',   'followers'],
            '%facebook%follower%'  => ['facebook',  'followers'],
            '%facebook%like%'      => ['facebook',  'likes'],
            '%twitter%follower%'   => ['twitter',   'followers'],
            '%twitter%like%'       => ['twitter',   'likes'],
        ];

        foreach ($map as $pattern => [$platform, $type]) {
            DB::table('categories')
                ->whereRaw('LOWER(name) LIKE ?', [$pattern])
                ->whereNull('platform')
                ->update(['platform' => $platform, 'type' => $type]);
        }

        // ── 2. SERVICES ──────────────────────────────────────────────────────
        Schema::table('services', function (Blueprint $table) {
            // Price index for cheapest-first default sort
            $table->index(['status', 'rate'], 'services_status_rate_idx');

            // Covering index for the most common filter combo
            $table->index(['category_id', 'status', 'rate'], 'services_cat_status_rate_idx');
        });

        // ── 3. FUND_ACCOUNTS ────────────────────────────────────────────────
        // Add is_active boolean alongside the existing enum status column.
        if (!Schema::hasColumn('fund_accounts', 'is_active')) {
            Schema::table('fund_accounts', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('status');
                $table->index('is_active', 'fund_accounts_is_active_idx');
            });

            /**
             * FIXED FOR POSTGRESQL:
             * We use 'true' and 'false' keywords instead of 1 and 0 
             * to avoid the "Datatype mismatch" error on Railway.
             */
            DB::table('fund_accounts')->update([
                'is_active' => DB::raw("CASE WHEN status = 'active' THEN true ELSE false END"),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_platform_type_status_idx');
            $table->dropColumn(['platform', 'type']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex('services_status_rate_idx');
            $table->dropIndex('services_cat_status_rate_idx');
        });

        if (Schema::hasColumn('fund_accounts', 'is_active')) {
            Schema::table('fund_accounts', function (Blueprint $table) {
                $table->dropIndex('fund_accounts_is_active_idx');
                $table->dropColumn('is_active');
            });
        }
    }
};
