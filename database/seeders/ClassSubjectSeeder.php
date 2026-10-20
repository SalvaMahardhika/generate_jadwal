<?php

namespace Database\Seeders;

use App\Models\Classes;
use App\Models\ClassSubject;
use App\Models\ClassSubjectTeacher;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class ClassSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $classes = Classes::with('grade')->get();
        $subjects = Subject::all()->keyBy('subject_code');
        $teachers = Teacher::all();

        // Ambil Guru Spesialis
        $pabpTeachers  = $teachers->whereIn('nip', ['198609202012011008'])->values(); // PABP & BTQ
        $pjokTeachers  = $teachers->whereIn('nip', ['199104122016011007'])->values(); // PJOK
        $bingTeachers  = $teachers->whereIn('nip', ['199406142020022009'])->values(); // B. Inggris
        $bjawaTeachers = $teachers->whereIn('nip', ['199307222019022010'])->values(); // B. Jawa
        $aiTeachers    = $teachers->whereIn('nip', ['199510102022011011'])->values(); // Koding & AI

        foreach ($classes as $class) {
            $sequence = $class->grade?->sequence ?? 1;

            /**
             * Distribusi Beban JP per Jenjang Kelas:
             * - Kelas 1: PABP (3), PPKN (4), BINDO (6), MTK (4), PJOK (3), SRUP (3), BJAWA (2), BTQ (2), KOKURIKULER (5)
             * - Kelas 2: PABP (3), PPKN (4), BINDO (7), MTK (5), PJOK (3), SRUP (3), BJAWA (2), BTQ (2), KOKURIKULER (5)
             * - Kelas 3-4: + IPAS (5), BING (2)
             * - Kelas 5-6: + Koding & AI (2)
             */
            $subjectJpMap = match ($sequence) {
                1 => [
                    'PABP' => 3, 'PPKN' => 4, 'BINDO' => 6, 'MTK' => 4, 'IPAS' => 0,
                    'PJOK' => 3, 'SRUP' => 3, 'BJAWA' => 2, 'BTQ' => 2, 'KOKURIKULER' => 5
                ],
                2 => [
                    'PABP' => 3, 'PPKN' => 4, 'BINDO' => 7, 'MTK' => 5, 'IPAS' => 0,
                    'PJOK' => 3, 'SRUP' => 3, 'BJAWA' => 2, 'BTQ' => 2, 'KOKURIKULER' => 5
                ],
                3, 4 => [
                    'PABP' => 3, 'PPKN' => 4, 'BINDO' => 6, 'MTK' => 5, 'IPAS' => 5,
                    'PJOK' => 3, 'SRUP' => 3, 'BING' => 2, 'BJAWA' => 2, 'BTQ' => 2, 'KOKURIKULER' => 5
                ],
                5, 6 => [
                    'PABP' => 3, 'PPKN' => 4, 'BINDO' => 6, 'MTK' => 5, 'IPAS' => 5,
                    'PJOK' => 3, 'SRUP' => 3, 'BING' => 2, 'BJAWA' => 2, 'BTQ' => 2,
                    'AI_KODING' => 2, 'KOKURIKULER' => 5
                ],
                default => []
            };

            foreach ($subjectJpMap as $code => $totalJp) {
                if ($totalJp <= 0 || !isset($subjects[$code])) {
                    continue;
                }

                $subject = $subjects[$code];

                // 1. Buat data ClassSubject
                $classSubject = ClassSubject::create([
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'total_jp' => $totalJp,
                    'max_jp_per_day' => ($code === 'KOKURIKULER') ? 5 : 2,
                    'status' => 'active',
                ]);

                // 2. Tentukan Guru Pengampu
                $assignedTeacherId = match ($code) {
                    'PABP', 'BTQ' => $pabpTeachers->first()?->id ?? $class->homeroom_teacher_id,
                    'PJOK'        => $pjokTeachers->first()?->id ?? $class->homeroom_teacher_id,
                    'BING'        => $bingTeachers->first()?->id ?? $class->homeroom_teacher_id,
                    'BJAWA'       => $bjawaTeachers->first()?->id ?? $class->homeroom_teacher_id,
                    'AI_KODING'   => $aiTeachers->first()?->id ?? $class->homeroom_teacher_id,
                    default       => $class->homeroom_teacher_id ?? $teachers->first()->id,
                };

                // 3. Buat data ClassSubjectTeacher
                ClassSubjectTeacher::create([
                    'class_subject_id' => $classSubject->id,
                    'teacher_id' => $assignedTeacherId,
                ]);
            }
        }
    }
}