<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        // FIXED HIGH-4: Replace N+1 queries with a single join/subquery
        $referrals = User::where('referred_by', $user->id)
            ->withCount('orders')
            ->select('users.*')
            ->selectSub(function ($query) use ($user) {
                $query->from('transactions')
                    ->where('user_id', $user->id)
                    ->where('type', 'referral_bonus')
                    ->whereRaw("description LIKE '%referral%' || users.id || '%'")
                    ->selectRaw('SUM(amount)');
            }, 'referral_commission')
            ->get();

        $stats = [
            'total_referrals' => $referrals->count(),
            'total_earned' => Transaction::where('user_id', $user->id)
                ->where('type', 'referral_bonus')
                ->sum('amount'),
            'earned_month' => Transaction::where('user_id', $user->id)
                ->where('type', 'referral_bonus')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
        ];

        return view('referrals.index', compact('referrals', 'stats'));
    }
}
