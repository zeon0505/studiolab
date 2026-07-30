<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'hari',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
