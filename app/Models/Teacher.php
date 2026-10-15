<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'nip',
        'name',
        'gender',
        'max_jp_per_week',
        'status',
    ];

    public function homeroomClasses()
    {
        return $this->hasMany(Classes::class, 'homeroom_teacher_id');
    }

    public function classSubjectTeachers()
    {
        return $this->hasMany(ClassSubjectTeacher::class);
    }
}