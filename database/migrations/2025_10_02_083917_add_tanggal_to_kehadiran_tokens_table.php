<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, clear any existing tokens to avoid conflicts
        DB::table('kehadiran_tokens')->delete();
        
        Schema::table('kehadiran_tokens', function (Blueprint $table) {
            $table->date('tanggal')->after('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kehadiran_tokens', function (Blueprint $table) {
            $table->dropColumn('tanggal');
        });
    }
};
