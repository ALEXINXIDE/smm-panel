<?php

/**
 * routes/web.php — additions / replacements for the SMM refactor
 * ─────────────────────────────────────────────────────────────────────────────
 * Drop this block into your existing routes/web.php, replacing or supplementing
 * any existing fund_accounts / services / funds routes.
 * ─────────────────────────────────────────────────────────────────────────────
 */

use App\Http\Controllers\Admin\FundAccountController as AdminFundAccountController;
use App\Http\Controllers\FundsController;
use App\Http\Controllers\ServiceController;

// ── User: Services (filtered, paginated) ─────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    Route::get('/services', [ServiceController::class, 'index'])
        ->name('services.index');

    // ── Funds ─────────────────────────────────────────────────────────────
    Route::prefix('funds')->name('funds.')->group(function () {
        Route::get('/',        [FundsController::class, 'index'])->name('index');
        Route::post('/manual', [FundsController::class, 'manual'])->name('manual');

        // Stripe & PayPal — only active when keys are set in .env
        Route::post('/stripe', [FundsController::class, 'stripe'])->name('stripe');
        Route::post('/paypal', [FundsController::class, 'paypal'])->name('paypal');
    });

});

// ── Admin: Fund Accounts (payment methods management) ─────────────────────────
Route::middleware(['auth', 'admin'])
    ->prefix('admin/fund-accounts')
    ->name('admin.fund_accounts.')
    ->group(function () {

        Route::get('/',                              [AdminFundAccountController::class, 'index'])  ->name('index');
        Route::get('/create',                        [AdminFundAccountController::class, 'create']) ->name('create');
        Route::post('/',                             [AdminFundAccountController::class, 'store'])  ->name('store');
        Route::get('/{fundAccount}/edit',            [AdminFundAccountController::class, 'edit'])   ->name('edit');
        Route::put('/{fundAccount}',                 [AdminFundAccountController::class, 'update']) ->name('update');
        Route::delete('/{fundAccount}',              [AdminFundAccountController::class, 'destroy'])->name('destroy');

        // AJAX toggle endpoint — POST /admin/fund-accounts/{id}/toggle
        Route::post('/{fundAccount}/toggle',         [AdminFundAccountController::class, 'toggle']) ->name('toggle');

    });
