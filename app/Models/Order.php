<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        'link',
        'quantity',
        'total',
        'status',
        'remains',
        'api_order_id',
        'start_count',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'total' => 'float',
        'remains' => 'integer',
        'start_count' => 'integer',
        'api_order_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'in progress']);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in progress');
    }
}
