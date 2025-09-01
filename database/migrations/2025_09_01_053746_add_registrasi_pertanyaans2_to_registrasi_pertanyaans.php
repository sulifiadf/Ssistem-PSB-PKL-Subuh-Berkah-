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
            $table->date('mulai_jual');
            $table->string('penjaga_stand');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrasi_pertanyaans', function (Blueprint $table) {
            $table->dropColumn('mulai_jual');
            $table->dropColumn('penjaga_stand');
        });
    }
};
