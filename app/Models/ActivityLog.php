<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ActivityLog model
 *
 * FIXES MEDIUM-5: AdminController imported `App\Models\Log as ActivityLog` which resolves
 * to the Laravel Log facade causing a fatal class-not-found error on the Activity Logs page.
 *
 * This class is the correct model backing the `logs` table.
 * Controller import changed to: use App\Models\ActivityLog;
 *
 * @property int         $id
 * @property int|null    $user_id
 * @property string      $action
 * @property string|null $entity_type
 * @property int|null    $entity_id
 * @property array|null  $changes
 * @property string|null $ip_address
 * @property string|null $user_agent
 */
class ActivityLog extends Model
{
    protected $table = 'logs';

    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'changes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'changes'    => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
