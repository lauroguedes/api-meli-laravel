<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    protected $fillable = [
        'token',
        'refresh_token',
        'integration_user_id',
        'app_id',
        'integration',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
