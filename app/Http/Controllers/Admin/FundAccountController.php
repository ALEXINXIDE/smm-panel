<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FundAccount;
use Illuminate\Http\Request;

/**
 * Admin\FundAccountController
 * ─────────────────────────────────────────────────────────────────────────────
 * Manages payment methods stored in the `fund_accounts` table.
 * Replaces the old PaymentAccountController which mixed two different models.
 * ─────────────────────────────────────────────────────────────────────────────
 */
class FundAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    // ── List ──────────────────────────────────────────────────────────────

    public function index()
    {
        $accounts = FundAccount::latest()->paginate(20);
        return view('admin.fund_accounts.index', compact('accounts'));
    }

    // ── Create ────────────────────────────────────────────────────────────

    public function create()
    {
        return view('admin.fund_accounts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'iban'           => 'nullable|string|max:50',
            'account_number' => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:500',
            'is_active'      => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['status']    = $validated['is_active'] ? 'active' : 'inactive';

        FundAccount::create($validated);

        return redirect()->route('admin.fund_accounts.index')
            ->with('success', 'Payment account created.');
    }

    // ── Edit ──────────────────────────────────────────────────────────────

    public function edit(FundAccount $fundAccount)
    {
        return view('admin.fund_accounts.edit', ['account' => $fundAccount]);
    }

    public function update(Request $request, FundAccount $fundAccount)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'iban'           => 'nullable|string|max:50',
            'account_number' => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:500',
            'is_active'      => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['status']    = $validated['is_active'] ? 'active' : 'inactive';

        $fundAccount->update($validated);

        // Bust the cache so FundsController picks up the change immediately
        \Illuminate\Support\Facades\Cache::forget('active_fund_accounts');

        return redirect()->route('admin.fund_accounts.index')
            ->with('success', 'Payment account updated.');
    }

    // ── Toggle (AJAX-friendly) ────────────────────────────────────────────

    /**
     * POST /admin/fund-accounts/{fundAccount}/toggle
     *
     * Flips is_active and returns JSON so the admin table can update
     * without a full page reload.
     */
    public function toggle(FundAccount $fundAccount)
    {
        $fundAccount->toggle();

        \Illuminate\Support\Facades\Cache::forget('active_fund_accounts');

        if (request()->expectsJson()) {
            return response()->json([
                'is_active' => $fundAccount->is_active,
                'message'   => $fundAccount->is_active
                    ? "{$fundAccount->name} enabled."
                    : "{$fundAccount->name} disabled.",
            ]);
        }

        return back()->with('success', $fundAccount->is_active
            ? "{$fundAccount->name} enabled."
            : "{$fundAccount->name} disabled."
        );
    }

    // ── Destroy ───────────────────────────────────────────────────────────

    public function destroy(FundAccount $fundAccount)
    {
        $fundAccount->delete();
        \Illuminate\Support\Facades\Cache::forget('active_fund_accounts');

        return redirect()->route('admin.fund_accounts.index')
            ->with('success', 'Payment account deleted.');
    }
}
