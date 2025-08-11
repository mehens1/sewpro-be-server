<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'gender',
        'profile_picture',
        'residential_address',
        'bio',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
