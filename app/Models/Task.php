<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'collection_date',
        'due_date',
        'status',
        'reminder_3d_sent_at',
        'reminder_2d_sent_at',
        'reminder_1d_sent_at',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'reminder_sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
