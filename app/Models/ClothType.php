<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClothType extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'name',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function measurements()
    {
        return $this->hasMany(Measurement::class);
    }
}
