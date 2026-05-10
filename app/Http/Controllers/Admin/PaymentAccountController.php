<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FundRequest;
use App\Models\PaymentAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of payment accounts (EasyPaisa, JazzCash, etc.)
     */
    public function index()
    {
        $accounts = PaymentAccount::withCount('fundRequests')->latest()->get();
        return view('admin.payment-accounts.index', compact('accounts'));
    }

    /**
     * Display all manual fund deposit requests from users
     */
    public function fundRequests()
    {
        $requests = FundRequest::with(['user', 'paymentAccount'])
            ->latest()
            ->paginate(20);
            
        return view('admin.fund-requests.index', compact('requests'));
    }

    /**
     * Approve a deposit and credit the user's balance
     */
    public function approve(Request $request, FundRequest $fundRequest)
    {
        if ($fundRequest->status !== 'pending') {
            return back()->withErrors(['error' => 'This request is already processed.']);
        }

        try {
            DB::transaction(function () use ($fundRequest) {
                // 1. Lock and update user balance
                $user = User::lockForUpdate()->findOrFail($fundRequest->user_id);
                $user->increment('funds', $fundRequest->usd_amount);

                // 2. Mark request as approved
                $fundRequest->update([
                    'status' => 'approved',
                    'reviewed_by' => Auth::id(),
                    'reviewed_at' => now(),
                ]);

                // 3. Create a transaction log
                Transaction::create([
                    'user_id' => $user->id,
                    'amount' => $fundRequest->usd_amount,
                    'type' => 'deposit',
                    'description' => 'Deposit approved (TID: ' . $fundRequest->transaction_id . ')',
                    'status' => 'completed',
                    'gateway' => 'manual'
                ]);
            });

            return back()->with('success', 'Balance credited successfully.');
        } catch (\Exception $e) {
            Log::error('Deposit Approval Failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'System error during approval.']);
        }
    }

    /**
     * Reject a deposit request
     */
    public function reject(Request $request, FundRequest $fundRequest)
    {
        $request->validate([
            'admin_note' => 'required|string|max:255'
        ]);

        $fundRequest->update([
            'status' => 'rejected',
            'admin_note' => $request->admin_note,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Request rejected.');
    }

    /**
     * Create a new payment account
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'type'           => 'required|in:easypaisa,jazzcash,bank,crypto',
            'account_number' => 'required|string|max:255',
            'account_title'  => 'nullable|string|max:100',
            'bank_name'      => 'nullable|string|max:100',
        ]);

        PaymentAccount::create($validated + ['is_active' => true]);
        return back()->with('success', 'Payment account added.');
    }
}
