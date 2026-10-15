<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('time_slot_id')->constrained('time_slots')->cascadeOnDelete();
            
            // Nullable karena slot bisa diisi kegiatan non-mapel (misal: Upacara/Dhuha)
            $table->foreignId('class_subject_teacher_id')
                  ->nullable()
                  ->constrained('class_subject_teachers')
                  ->nullOnDelete();
                  
            $table->boolean('is_locked')->default(false); // Penanda slot terkunci
            $table->string('custom_activity')->nullable(); // Misal: "Upacara", "Sholat Dhuha"
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};