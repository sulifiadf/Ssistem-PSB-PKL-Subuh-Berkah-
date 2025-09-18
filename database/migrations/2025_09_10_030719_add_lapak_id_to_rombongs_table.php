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
            $table->unsignedBigInteger('lapak_id')->nullable()->after('rombong_id');
            $table->foreign('lapak_id')->references('lapak_id')->on('lapaks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rombongs', function (Blueprint $table) {
            $table->dropForeign(['lapak_id']);
            $table->dropColumn('lapak_id');
        });
    }
};
