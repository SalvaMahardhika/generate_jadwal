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
        Schema::create('published_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('time_slot_id')->constrained('time_slots')->cascadeOnDelete();

            // Nullable karena slot bisa diisi kegiatan non-mapel (misal: Upacara/Dhuha)
            $table->foreignId('class_subject_teacher_id')
                  ->nullable()
                  ->constrained('class_subject_teachers')
                  ->nullOnDelete();

            $table->boolean('is_locked')->default(false);
            $table->string('custom_activity')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // Mencegah duplikasi slot di kelas dan tahun ajaran yang sama
            $table->unique(
                ['academic_year_id', 'class_id', 'time_slot_id'],
                'published_schedules_year_class_slot_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('published_schedules');
    }
};