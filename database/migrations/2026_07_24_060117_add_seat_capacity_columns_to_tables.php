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
        Schema::table('items', function (Blueprint $table) {
            $table->integer('kapasitas_kursi')->default(0)->after('stok');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->integer('jumlah_kursi')->default(0)->after('jam_selesai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('kapasitas_kursi');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('jumlah_kursi');
        });
    }
};
