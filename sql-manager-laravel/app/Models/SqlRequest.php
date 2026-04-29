<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SqlRequest extends Model
{
    //
    protected $fillable = [
        'user_id',
        'sql_text',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
