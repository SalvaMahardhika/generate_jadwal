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
        Schema::table('subjects', function (Blueprint $table) {
            $table->boolean('is_joint_subject')
                ->default(false)
                ->after('status')
                ->comment('Flag apakah mapel ini bisa diampu secara bersamaan/paralel lintas kelas oleh guru yang sama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('is_joint_subject');
        });
    }
};