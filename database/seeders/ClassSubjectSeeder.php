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

        // Ambil Guru Spesialis berdasarkan NIP
        $pabpTeachers  = $teachers->whereIn('nip', ['198609202012011008'])->values(); // PABP & BTQ
        $pjokTeachers  = $teachers->whereIn('nip', ['199104122016011007'])->values(); // PJOK
        $bingTeachers  = $teachers->whereIn('nip', ['199406142020022009'])->values(); // B. Inggris / Seni
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

                // 2. Tentukan Alokasi Guru Pengampu & Skenario Team Teaching
                $homeroomTeacherId = $class->homeroom_teacher_id ?? $teachers->first()->id;

                if ($code === 'KOKURIKULER') {
                    // Skenario Team Teaching 1: Kokurikuler (5 JP) -> Wali Kelas (3 JP) + Guru Seni/Inggris (2 JP)
                    $assistantTeacherId = $bingTeachers->first()?->id ?? $teachers->last()->id;

                    ClassSubjectTeacher::create([
                        'class_subject_id' => $classSubject->id,
                        'teacher_id' => $homeroomTeacherId,
                        'jp' => 3,
                    ]);

                    ClassSubjectTeacher::create([
                        'class_subject_id' => $classSubject->id,
                        'teacher_id' => $assistantTeacherId,
                        'jp' => 2,
                    ]);
                } elseif ($code === 'BINDO' && $sequence >= 3) {
                    // Skenario Team Teaching 2: Bahasa Indonesia Kelas Tinggi (6 JP) -> Wali Kelas (4 JP) + Guru B. Jawa (2 JP)
                    $assistantTeacherId = $bjawaTeachers->first()?->id ?? $teachers->last()->id;

                    ClassSubjectTeacher::create([
                        'class_subject_id' => $classSubject->id,
                        'teacher_id' => $homeroomTeacherId,
                        'jp' => 4,
                    ]);

                    ClassSubjectTeacher::create([
                        'class_subject_id' => $classSubject->id,
                        'teacher_id' => $assistantTeacherId,
                        'jp' => 2,
                    ]);
                } else {
                    // Single Teacher (Guru Tunggal) -> Mengajar seluruh total_jp mapel
                    $assignedTeacherId = match ($code) {
                        'PABP', 'BTQ' => $pabpTeachers->first()?->id ?? $homeroomTeacherId,
                        'PJOK'        => $pjokTeachers->first()?->id ?? $homeroomTeacherId,
                        'BING'        => $bingTeachers->first()?->id ?? $homeroomTeacherId,
                        'BJAWA'       => $bjawaTeachers->first()?->id ?? $homeroomTeacherId,
                        'AI_KODING'   => $aiTeachers->first()?->id ?? $homeroomTeacherId,
                        default       => $homeroomTeacherId,
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