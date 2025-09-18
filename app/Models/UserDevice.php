<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'token', 'platform', 'device_id', 'last_seen_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
