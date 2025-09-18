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
        Schema::table('waiting_lists', function (Blueprint $table) {
            $table->dropForeign(['rombong_id']);
            $table->dropColumn('rombong_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waiting_lists', function (Blueprint $table) {
            $table->unsignedBigInteger('rombong_id')->nullable()->after('user_id');
            $table->foreign('rombong_id')->references('rombong_id')->on('rombongs')->onDelete('cascade');
        });
    }
};
