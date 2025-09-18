<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'address',
        'phone',
        'email',
        'logo_path',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
