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
    ];

    public function classSubjects()
    {
        return $this->hasMany(ClassSubject::class);
    }
}