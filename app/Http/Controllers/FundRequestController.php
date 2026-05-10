<?php
namespace App\Http\Controllers;

use App\Models\FundRequest;
use App\Models\PaymentAccount;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FundRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $accounts   = PaymentAccount::where('is_active', true)->get();
        $myRequests = FundRequest::where('user_id', Auth::id())
            ->with('paymentAccount')
            ->latest()
            ->take(10)
            ->get();
        $rate       = session('usd_pkr_rate', 280);
        $waNumber   = SiteSetting::get('whatsapp_number', '');
        $waMessage  = SiteSetting::get('whatsapp_message', 'Hi, I submitted a fund request. TXN ID: ');

        return view('funds.manual', compact('accounts', 'myRequests', 'rate', 'waNumber', 'waMessage'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_account_id' => 'required|exists:payment_accounts,id',
            'amount'             => 'required|numeric|min:100|max:500000',
            'transaction_id'     => 'required|string|min:4|max:100',
        ]);

        // Prevent duplicate TXN ID submissions
        if (FundRequest::where('transaction_id', $validated['transaction_id'])->exists()) {
            return back()
                ->withErrors(['transaction_id' => 'This transaction ID has already been submitted.'])
                ->withInput();
        }

        $rate      = session('usd_pkr_rate', 280);
        $usdAmount = round($validated['amount'] / $rate, 6);

        FundRequest::create([
            'user_id'            => Auth::id(),
            'payment_account_id' => $validated['payment_account_id'],
            'amount'             => $validated['amount'],
            'usd_amount'         => $usdAmount,
            'transaction_id'     => $validated['transaction_id'],
            'status'             => 'pending',
        ]);

        return back()->with('success', 'Fund request submitted! Admin will review and credit your account shortly.');
    }
}
