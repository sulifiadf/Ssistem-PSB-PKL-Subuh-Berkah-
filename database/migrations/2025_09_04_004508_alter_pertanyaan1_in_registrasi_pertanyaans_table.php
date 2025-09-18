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
        Schema::table('registrasi_pertanyaans', function (Blueprint $table) {
            $table->json('pertanyaan1')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrasi_pertanyaans', function (Blueprint $table) {
            $table->string('pertanyaan1')->change();
        });
    }
};
