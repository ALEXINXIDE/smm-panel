<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $fillable = ['user_id','subject','message','category','order_id','status'];
    public function user(): BelongsTo      { return $this->belongsTo(User::class); }
    public function messages(): HasMany    { return $this->hasMany(TicketMessage::class)->orderBy('created_at'); }
}
