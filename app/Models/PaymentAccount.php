<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentAccount extends Model
{
    protected $fillable = ['name','type','account_number','account_title','bank_name','is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function fundRequests(): HasMany { return $this->hasMany(FundRequest::class); }

    public function typeLabel(): string
    {
        return match($this->type) {
            'easypaisa' => 'EasyPaisa',
            'jazzcash'  => 'JazzCash',
            'bank'      => 'Bank Transfer',
            'crypto'    => 'Crypto',
            default     => ucfirst($this->type),
        };
    }
}
