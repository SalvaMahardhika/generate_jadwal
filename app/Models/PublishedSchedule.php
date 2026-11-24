<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublishedSchedule extends Model
{
    use HasFactory;

    protected $table = 'published_schedules';

    protected $fillable = [
        'academic_year_id',
        'class_id',
        'time_slot_id',
        'class_subject_teacher_id',
        'is_locked',
        'custom_activity',
        'created_by',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class, 'time_slot_id');
    }

    public function classSubjectTeacher(): BelongsTo
    {
        return $this->belongsTo(ClassSubjectTeacher::class, 'class_subject_teacher_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}