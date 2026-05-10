<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApiProvider extends Model
{
    protected $fillable = ['name','url','api_key','status','percentage_increase'];
    protected $casts    = ['api_key'=>'encrypted','percentage_increase'=>'float'];
    public function services(): HasMany { return $this->hasMany(Service::class); }
    public function scopeActive($q)     { return $q->where('status','active'); }
}
