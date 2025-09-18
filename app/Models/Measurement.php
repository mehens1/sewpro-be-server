<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'cloth_type_id',
        'field_name',
        'field_value',
    ];

    public function clothType()
    {
        return $this->belongsTo(ClothType::class);
    }
}
