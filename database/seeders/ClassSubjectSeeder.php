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

        // Ambil guru spesialis berdasarkan NIP / urutan
        $pabpTeachers = $teachers->whereIn('nip', ['198609202012011008', '199005152018011003'])->values(); // 2 Guru Agama
        $pjokTeachers = $teachers->whereIn('nip', ['199104122016011007', '198711052011011005'])->values(); // 2 Guru PJOK
        $bingTeachers = $teachers->whereIn('nip', ['199406142020022009', '198902182015022006'])->values(); // 2 Guru B. Inggris

        foreach ($classes as $class) {
            $sequence = $class->grade?->sequence ?? 1;

            // Beban JP Kurikulum Merdeka SD
            $subjectJpMap = [
                'PABP'  => 3,
                'PPKN'  => 4,
                'BINDO' => 6,
                'MTK'   => ($sequence <= 1) ? 4 : 5,
                'IPAS'  => ($sequence <= 2) ? 0 : 5, // IPAS mulai Kelas 3
                'PJOK'  => 3,
                'SRUP'  => 3,
                'BING'  => 2,
                'BJAWA' => 2,
            ];

            foreach ($subjects as $code => $subject) {
                $totalJp = $subjectJpMap[$code] ?? 2;

                if ($totalJp <= 0) {
                    continue; // Lewati jika JP = 0 (misal IPAS di Kelas 1 & 2)
                }

                // 1. Buat data ClassSubject
                $classSubject = ClassSubject::create([
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'total_jp' => $totalJp,
                    'max_jp_per_day' => 2,
                    'status' => 'active',
                ]);

                // 2. Penentuan Guru Pengampu yang Variatif Antar Kelas:
                // - Kelas 1-3 (Fase A & B awal) dan Kelas 4-6 (Fase B akhir & C) beda guru pengampu
                $isUpperGrade = $sequence > 3;

                $assignedTeacherId = match ($code) {
                    // Agama: Kelas 1-3 diampu Guru Agama 1, Kelas 4-6 diampu Guru Agama 2
                    'PABP'  => $pabpTeachers->get($isUpperGrade ? 1 : 0)?->id ?? $pabpTeachers->first()?->id,
                    
                    // PJOK: Kelas 1-3 diampu Guru PJOK 1, Kelas 4-6 diampu Guru PJOK 2
                    'PJOK'  => $pjokTeachers->get($isUpperGrade ? 1 : 0)?->id ?? $pjokTeachers->first()?->id,
                    
                    // B. Inggris: Kelas 1-3 diampu Guru BING 1, Kelas 4-6 diampu Guru BING 2
                    'BING'  => $bingTeachers->get($isUpperGrade ? 1 : 0)?->id ?? $bingTeachers->first()?->id,
                    
                    // Mapel Umum (BINDO, MTK, PPKN, IPAS, SRUP, BJAWA): Diampu Wali Kelas Masing-masing (Beda Guru per Kelas)
                    default => $class->homeroom_teacher_id ?? $teachers->first()->id,
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