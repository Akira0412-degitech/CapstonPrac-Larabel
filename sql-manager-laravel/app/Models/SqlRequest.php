<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SqlRequest extends Model
{
    //
    protected $fillable = [
        'user_id',
        'sql_text',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'request_id');
    }

    public function decisionLog(): HasOne
    {
        return $this->hasOne(AuditLog::class, 'request_id')
            ->whereIn('action', ['approved', 'rejected'])
            ->latestOfMany();
    }
}
