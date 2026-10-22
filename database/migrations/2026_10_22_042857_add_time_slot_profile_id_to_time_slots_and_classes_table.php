<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Hubungkan Slot Waktu ke Profilnya
        Schema::table('time_slots', function (Blueprint $table) {
            $table->foreignId('time_slot_profile_id')
                ->after('id')
                ->constrained('time_slot_profiles')
                ->cascadeOnDelete();
        });

        // 2. Hubungkan Kelas ke Profil Slot Waktu yang Digunakan
        Schema::table('classes', function (Blueprint $table) {
            $table->foreignId('time_slot_profile_id')
                ->nullable()
                ->after('academic_year_id')
                ->constrained('time_slot_profiles')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('time_slots', function (Blueprint $table) {
            $table->dropForeign(['time_slot_profile_id']);
            $table->dropColumn('time_slot_profile_id');
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['time_slot_profile_id']);
            $table->dropColumn('time_slot_profile_id');
        });
    }
};