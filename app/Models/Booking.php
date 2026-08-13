<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'user_id',
        'penanggung_jawab_id',
        'nama_peminjam',
        'instansi_peminjam',
        'no_wa',
        'bukti_peminjam',
        'tanggal_peminjaman',
        'tanggal_pengembalian',
        'jam_mulai',
        'jam_selesai',
        'jumlah_kursi',
        'status',
        'catatan',
        'reminder_sent',
        'foto_pengembalian',
    ];

    protected $casts = [
        'tanggal_peminjaman' => 'date',
        'tanggal_pengembalian' => 'date',
    ];

    public function items()
    {
        return $this->belongsToMany(Item::class, 'booking_items')->withPivot('jumlah')->withTimestamps();
    }

    public function bookingItems()
    {
        return $this->hasMany(BookingItem::class);
    }

    // Relasi singular: untuk backward compat atau booking ruangan yang punya 1 item_id
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function penanggungJawab()
    {
        return $this->belongsTo(User::class, 'penanggung_jawab_id');
    }

    public function bookingLogs()
    {
        return $this->hasMany(BookingLog::class)->latest();
    }
}
