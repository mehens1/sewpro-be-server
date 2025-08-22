<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'phone',
        'gender',
        'date_of_birth',
        'profile_picture',
        'nationality',
        'address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
