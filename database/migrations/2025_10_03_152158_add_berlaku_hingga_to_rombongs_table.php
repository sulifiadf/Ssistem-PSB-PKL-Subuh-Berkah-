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
        Schema::table('rombongs', function (Blueprint $table) {
            $table->date('berlaku_hingga')->nullable()->after('jenis')
                  ->comment('Tanggal berlaku untuk anggota sementara (null = permanent)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rombongs', function (Blueprint $table) {
            $table->dropColumn('berlaku_hingga');
        });
    }
};
