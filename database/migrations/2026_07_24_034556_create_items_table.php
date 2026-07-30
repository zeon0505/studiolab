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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('kategori', ['studio', 'laboratorium']);
            $table->enum('tipe', ['peralatan', 'ruangan']);
            $table->text('deskripsi')->nullable();
            $table->string('gambar')->nullable();
            $table->string('status')->default('tersedia'); // tersedia, dipinjam, maintenance
            $table->integer('stok')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
