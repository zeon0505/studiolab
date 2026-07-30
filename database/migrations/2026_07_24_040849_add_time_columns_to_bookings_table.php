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
        Schema::table('bookings', function (Blueprint $table) {
            $table->time('jam_mulai')->nullable()->after('tanggal_pengembalian');
            $table->time('jam_selesai')->nullable()->after('jam_mulai');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade')->after('item_id');
            $table->foreignId('penanggung_jawab_id')->nullable()->constrained('users')->onDelete('set null')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['penanggung_jawab_id']);
            $table->dropColumn(['jam_mulai', 'jam_selesai', 'user_id', 'penanggung_jawab_id']);
        });
    }
};
