<?php

namespace Database\Seeders;

use App\Models\Classes;
use App\Models\ClassSubject;
use App\Models\ClassSubjectTeacher;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ClassSubjectSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        ClassSubjectTeacher::truncate();
        ClassSubject::truncate();
        Schema::enableForeignKeyConstraints();

        $classes = Classes::with('grade')->get();
        $subjects = Subject::all()->keyBy('subject_code');
        $teachers = Teacher::all();

        // Guru Spesialis
        $pabpTeachers  = $teachers->whereIn('nip', ['198609202012011008'])->values(); // PABP
        $pjokTeachers  = $teachers->whereIn('nip', ['199104122016011007'])->values(); // PJOK
        $bingTeachers  = $teachers->whereIn('nip', ['199406142020022009'])->values(); // B. Inggris & Seni
        $bjawaTeachers = $teachers->whereIn('nip', ['199307222019022010'])->values(); // Bahasa Jawa
        $aiTeachers    = $teachers->whereIn('nip', ['199510102022011011'])->values(); // Koding & AI

        // Tim Utama Kokurikuler (Team Teaching Paralel Lintas Kelas)
        $kokurikulerLeadTeacher = $bingTeachers->first() ?? $teachers->first();
        $kokurikulerCoTeacher   = $bjawaTeachers->first() ?? $teachers->last();

        foreach ($classes as $class) {
            $sequence = $class->grade?->sequence ?? 1;

            /**
             * Perhitungan JP Akurat Berdasarkan Permendikdasmen No 13/2025 (Di luar Upacara/Litnum):
             */
            $subjectJpMap = match ($sequence) {
                1 => [
                    'PABP' => 3, 'PPKN' => 4, 'BINDO' => 7, 'MTK' => 4,
                    'PJOK' => 3, 'SENI' => 3, 'BJAWA' => 2, 'KOKURIKULER' => 6
                ],
                2 => [
                    'PABP' => 3, 'PPKN' => 4, 'BINDO' => 8, 'MTK' => 5,
                    'PJOK' => 3, 'SENI' => 3, 'BJAWA' => 2, 'KOKURIKULER' => 6
                ],
                3, 4 => [
                    'PABP' => 3, 'PPKN' => 4, 'BINDO' => 6, 'MTK' => 5,
                    'IPAS' => 5, 'PJOK' => 3, 'SENI' => 3, 'BING' => 2,
                    'BJAWA' => 2, 'KOKURIKULER' => 7
                ],
                5, 6 => [
                    'PABP' => 3, 'PPKN' => 4, 'BINDO' => 6, 'MTK' => 5,
                    'IPAS' => 5, 'PJOK' => 3, 'SENI' => 3, 'BING' => 2,
                    'AI_KODING' => 2, 'BJAWA' => 2, 'KOKURIKULER' => 7
                ],
                default => []
            };

            foreach ($subjectJpMap as $code => $totalJp) {
                if ($totalJp <= 0 || !isset($subjects[$code])) {
                    continue;
                }

                $subject = $subjects[$code];

                $classSubject = ClassSubject::create([
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'total_jp' => $totalJp,
                    'max_jp_per_day' => ($code === 'KOKURIKULER') ? 7 : 4,
                    'status' => 'active',
                ]);

                $homeroomTeacherId = $class->homeroom_teacher_id ?? $teachers->first()->id;

                if ($code === 'KOKURIKULER') {
                    // Team Teaching Kokurikuler di Hari Sabtu
                    $leadJp = (int) ceil($totalJp / 2);
                    $coJp   = (int) floor($totalJp / 2);

                    ClassSubjectTeacher::create([
                        'class_subject_id' => $classSubject->id,
                        'teacher_id' => $kokurikulerLeadTeacher->id,
                        'jp' => $leadJp,
                    ]);

                    ClassSubjectTeacher::create([
                        'class_subject_id' => $classSubject->id,
                        'teacher_id' => $kokurikulerCoTeacher->id,
                        'jp' => $coJp,
                    ]);
                } else {
                    $assignedTeacherId = match ($code) {
                        'PABP'       => $pabpTeachers->first()?->id ?? $homeroomTeacherId,
                        'PJOK'       => $pjokTeachers->first()?->id ?? $homeroomTeacherId,
                        'BING'       => $bingTeachers->first()?->id ?? $homeroomTeacherId,
                        'BJAWA'      => $bjawaTeachers->first()?->id ?? $homeroomTeacherId,
                        'AI_KODING'  => $aiTeachers->first()?->id ?? $homeroomTeacherId,
                        default      => $homeroomTeacherId,
                    };

                    ClassSubjectTeacher::create([
                        'class_subject_id' => $classSubject->id,
                        'teacher_id' => $assignedTeacherId,
                        'jp' => $totalJp,
                    ]);
                }
            }
        }
    }
}