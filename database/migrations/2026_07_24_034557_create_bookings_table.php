<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->string('nama_peminjam');
            $table->string('instansi_peminjam');
            $table->string('bukti_peminjam'); // Path KTM/KTP
            $table->date('tanggal_peminjaman');
            $table->date('tanggal_pengembalian');
            $table->string('status')->default('pending'); // pending, disetujui, ditolak, selesai
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
