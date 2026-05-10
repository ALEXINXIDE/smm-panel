<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = ['user_id','amount','type','description','status','reference','gateway'];
    protected $casts    = ['amount' => 'float'];
    public function user(): BelongsTo  { return $this->belongsTo(User::class); }
    public function scopeCompleted($q) { return $q->where('status', 'completed'); }
}
