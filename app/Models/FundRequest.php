<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundRequest extends Model
{
    protected $fillable = [
        'user_id','payment_account_id','amount','usd_amount',
        'transaction_id','status','admin_note','reviewed_by','reviewed_at',
    ];
    protected $casts = [
        'reviewed_at' => 'datetime',
        'amount'      => 'float',
        'usd_amount'  => 'float',
    ];

    public function user(): BelongsTo           { return $this->belongsTo(User::class); }
    public function paymentAccount(): BelongsTo { return $this->belongsTo(PaymentAccount::class); }
    public function reviewer(): BelongsTo       { return $this->belongsTo(User::class, 'reviewed_by'); }
}
