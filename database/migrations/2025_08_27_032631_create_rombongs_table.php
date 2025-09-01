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
        Schema::create('rombongs', function (Blueprint $table) {
            $table->id('rombong_id');
            $table->foreignId('user_id')->constrained('users', 'user_id');
            $table->string('foto_rombong');
            $table->string('foto_tetangga_kanan')->nullable();
            $table->string('foto_tetangga_kiri')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('jenis',['tetap', 'sementara']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rombongs');
    }
};
