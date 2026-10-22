<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'time_slot_profile_id',
        'day',
        'period_number',
        'label',
        'start_time',
        'end_time',
        'is_break',
    ];

    protected $casts = [
        'is_break' => 'boolean',
    ];

    public function timeSlotProfile()
    {
        return $this->belongsTo(TimeSlotProfile::class, 'time_slot_profile_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}