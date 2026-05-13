<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'done_by',
        'request_id',
        'action',
        'created_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'done_by');
    }

    public function sqlRequest(): BelongsTo
    {
        return $this->belongsTo(SqlRequest::class, 'request_id');
    }
}
