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
        $pabpTeachers  = $teachers->whereIn('nip', ['198609202012011008'])->values(); // PABP
        $pjokTeachers  = $teachers->whereIn('nip', ['199104122016011007'])->values(); // PJOK
        $bingTeachers  = $teachers->whereIn('nip', ['199406142020022009'])->values(); // B. Inggris & Seni
        $bjawaTeachers = $teachers->whereIn('nip', ['199307222019022010'])->values(); // Bahasa Jawa
        $aiTeachers    = $teachers->whereIn('nip', ['199510102022011011'])->values(); // Koding & AI

        // Tim Utama Kokurikuler
        $kokurikulerLeadTeacher = $bingTeachers->first() ?? $teachers->first();
        $kokurikulerCoTeacher   = $bjawaTeachers->first() ?? $teachers->last();

        foreach ($classes as $class) {
            $sequence = $class->grade?->sequence ?? 1;

            /**
             * Pemetaan Beban JP yang Akurat Sesuai Jadwal Kurikulum Merdeka:
             * Mulok diset khusus menggunakan kode BJAWA (Bahasa Jawa - 2 JP)
             */
            $subjectJpMap = match ($sequence) {
                1 => [
                    'UPACARA_LITNUM' => 6, 'PABP' => 3, 'PPKN' => 4, 'BINDO' => 6, 'MTK' => 4,
                    'PJOK' => 3, 'SENI' => 4, 'BJAWA' => 2, 'KOKURIKULER' => 6
                ],
                2 => [
                    'UPACARA_LITNUM' => 6, 'PABP' => 3, 'PPKN' => 4, 'BINDO' => 7, 'MTK' => 5,
                    'PJOK' => 3, 'SENI' => 3, 'BJAWA' => 2, 'KOKURIKULER' => 6
                ],
                3, 4 => [
                    'UPACARA_LITNUM' => 6, 'PABP' => 3, 'PPKN' => 4, 'BINDO' => 6, 'MTK' => 5,
                    'IPAS' => 5, 'PJOK' => 3, 'SENI' => 3, 'BING' => 2, 'BJAWA' => 2, 'KOKURIKULER' => 6
                ],
                5, 6 => [
                    'UPACARA_LITNUM' => 6, 'PABP' => 3, 'PPKN' => 4, 'BINDO' => 6, 'MTK' => 5,
                    'IPAS' => 5, 'PJOK' => 3, 'SENI' => 3, 'BING' => 2, 'AI_KODING' => 2,
                    'BJAWA' => 2, 'KOKURIKULER' => 6
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
                    'max_jp_per_day' => ($code === 'KOKURIKULER' || $code === 'UPACARA_LITNUM') ? 6 : 3,
                    'status' => 'active',
                ]);

                // 2. Alokasi Guru Pengampu & Team Teaching
                $homeroomTeacherId = $class->homeroom_teacher_id ?? $teachers->first()->id;

                if ($code === 'KOKURIKULER') {
                    // Team Teaching Kokurikuler (6 JP di Hari Sabtu)
                    ClassSubjectTeacher::create([
                        'class_subject_id' => $classSubject->id,
                        'teacher_id' => $kokurikulerLeadTeacher->id,
                        'jp' => 3,
                    ]);

                    ClassSubjectTeacher::create([
                        'class_subject_id' => $classSubject->id,
                        'teacher_id' => $kokurikulerCoTeacher->id,
                        'jp' => 3,
                    ]);
                } else {
                    // Single Teacher (Guru Tunggal)
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