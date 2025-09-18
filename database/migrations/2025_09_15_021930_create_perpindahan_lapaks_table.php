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
        Schema::create('perpindahan_lapaks', function (Blueprint $table) {
            $table->id('perpindahan_lapak_id');
            $table->foreignId('lapak_asal_id')->constrained('lapaks', 'lapak_id')->onDelete('cascade');
            $table->foreignId('lapak_tujuan_id')->constrained('lapaks', 'lapak_id')->onDelete('cascade');
            $table->foreignId('rombong_id')->constrained('rombongs', 'rombong_id')->onDelete('cascade');
            $table->dateTime('tanggal_perpindahan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perpindahan_lapaks');
    }
};
