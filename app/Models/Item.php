<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kategori',
        'tipe',
        'deskripsi',
        'gambar',
        'status',
        'stok',
        'kapasitas_kursi',
    ];

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_items')->withPivot('jumlah')->withTimestamps();
    }
}
