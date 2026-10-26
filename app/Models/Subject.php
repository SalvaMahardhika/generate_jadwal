<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_code',
        'subject_name',
        'color_code',
        'category',
        'passing_grade',
        'status',
        'is_joint_subject',
    ];

    protected $casts = [
        'is_joint_subject' => 'boolean',
        'passing_grade'    => 'integer',
    ];

    public function classSubjects()
    {
        return $this->hasMany(ClassSubject::class);
    }
}