<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlotProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'name',
        'description',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class, 'time_slot_profile_id');
    }

    public function classes()
    {
        return $this->hasMany(Classes::class, 'time_slot_profile_id');
    }
}