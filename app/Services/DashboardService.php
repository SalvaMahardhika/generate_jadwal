<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Teacher;

class DashboardService
{
    /**
     * Mengambil ringkasan statistik master data dan periode aktif.
     */
    public function getStats(): array
    {
        $teachersCount = Teacher::count();
        $classesCount = Classes::count();
        $subjectsCount = Subject::count();

        $activeAY = AcademicYear::where('is_active', true)->first();
        $activeAcademicYearLabel = '-';

        if ($activeAY) {
            $semesterLabel = $activeAY->semester === 'odd' ? 'Ganjil' : 'Genap';
            $activeAcademicYearLabel = "{$activeAY->name} {$semesterLabel}";
        }

        return [
            'teachers' => $teachersCount,
            'classes' => $classesCount,
            'subjects' => $subjectsCount,
            'active_academic_year' => $activeAcademicYearLabel,
        ];
    }
}