import ExcelJS from 'exceljs';
import type { TimeSlot, ScheduleItem } from '../types/wizard';
import type { ClassItem } from '../types/class';

export interface ExportParams {
  classes: ClassItem[];
  timeSlots: TimeSlot[];
  matrix: Record<number, Record<number, ScheduleItem>>;
  academicYearName: string;
}

/**
 * Konversi kode warna HEX ke format ARGB ExcelJS
 */
function hexToArgb(hex?: string): string {
  if (!hex) return 'FFE2E8F0';
  let cleanHex = hex.replace('#', '').trim();
  if (cleanHex.length === 3) {
    cleanHex = cleanHex.split('').map((c) => c + c).join('');
  }
  if (cleanHex.length === 6) {
    return 'FF' + cleanHex.toUpperCase();
  }
  return 'FFE2E8F0';
}

/**
 * Menentukan warna teks (Hitam/Putih) berdasarkan tingkat kecerahan background
 */
function getContrastTextColor(hex?: string): string {
  if (!hex) return 'FF000000';
  let cleanHex = hex.replace('#', '').trim();
  if (cleanHex.length === 3) {
    cleanHex = cleanHex.split('').map((c) => c + c).join('');
  }
  if (cleanHex.length !== 6) return 'FF000000';
  const r = parseInt(cleanHex.substring(0, 2), 16);
  const g = parseInt(cleanHex.substring(2, 4), 16);
  const b = parseInt(cleanHex.substring(4, 6), 16);
  const brightness = (r * 299 + g * 587 + b * 114) / 1000;
  return brightness >= 128 ? 'FF000000' : 'FFFFFFFF';
}

/**
 * Style Border Standar Tegas
 */
const darkBorderStyle: ExcelJS.Borders = {
  top: { style: 'thin', color: { argb: 'FF475569' } },
  left: { style: 'thin', color: { argb: 'FF475569' } },
  bottom: { style: 'thin', color: { argb: 'FF475569' } },
  right: { style: 'thin', color: { argb: 'FF475569' } },
};

/**
 * Helper untuk Ekspor Jadwal ke Berkas Excel (.xlsx) Multi-Sheet per Jurusan
 */
export async function exportScheduleToExcel(
  classesOrParams: ClassItem[] | ExportParams,
  timeSlotsParam?: TimeSlot[],
  matrixParam?: Record<number, Record<number, ScheduleItem>>,
  academicYearNameParam?: string
): Promise<void> {
  let classes: ClassItem[];
  let timeSlots: TimeSlot[];
  let matrix: Record<number, Record<number, ScheduleItem>>;
  let academicYearName: string;

  if (Array.isArray(classesOrParams)) {
    classes = classesOrParams;
    timeSlots = timeSlotsParam || [];
    matrix = matrixParam || {};
    academicYearName = academicYearNameParam || '';
  } else {
    classes = classesOrParams.classes || [];
    timeSlots = classesOrParams.timeSlots || [];
    matrix = classesOrParams.matrix || {};
    academicYearName = classesOrParams.academicYearName || '';
  }

  const workbook = new ExcelJS.Workbook();
  workbook.creator = 'Sistem Jadwal Pelajaran';
  workbook.created = new Date();

  // Grouping kelas berdasarkan Jurusan
  const classesByMajor = new Map<string, ClassItem[]>();

  classes.forEach((cls) => {
    const majorName = cls.major?.major_name || 'Umum';
    if (!classesByMajor.has(majorName)) {
      classesByMajor.set(majorName, []);
    }
    classesByMajor.get(majorName)!.push(cls);
  });

  const days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
  const dayLabels = ['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'];

  classesByMajor.forEach((classList, majorName) => {
    const safeSheetName = majorName.replace(/[:\\/?*\[\]]/g, '').substring(0, 30);
    const worksheet = workbook.addWorksheet(safeSheetName || 'Jadwal');

    // Pengaturan Lebar Kolom
    worksheet.columns = [
      { width: 18 }, // Jam / Waktu
      { width: 25 }, // Senin
      { width: 25 }, // Selasa
      { width: 25 }, // Rabu
      { width: 25 }, // Kamis
      { width: 25 }, // Jumat
      { width: 25 }, // Sabtu
    ];

    let currentRow = 1;

    classList.forEach((cls) => {
      // 1. Header Judul Kelas (Center, Background Abu-abu, Border Tegas)
      worksheet.mergeCells(currentRow, 1, currentRow, 7);
      const titleRow = worksheet.getRow(currentRow);
      titleRow.height = 25;
      for (let c = 1; c <= 7; c++) {
        const cell = titleRow.getCell(c);
        if (c === 1) {
          cell.value = `JADWAL PELAJARAN KELAS: ${cls.class_name.toUpperCase()}`;
        }
        cell.font = { bold: true, size: 11, color: { argb: 'FF1E293B' } };
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFE2E8F0' } };
        cell.alignment = { vertical: 'middle', horizontal: 'center' };
        cell.border = darkBorderStyle;
      }
      currentRow++;

      // Subtitle Kelas (Center, Background Abu-abu Muda, Border Tegas)
      worksheet.mergeCells(currentRow, 1, currentRow, 7);
      const subTitleRow = worksheet.getRow(currentRow);
      subTitleRow.height = 20;
      for (let c = 1; c <= 7; c++) {
        const cell = subTitleRow.getCell(c);
        if (c === 1) {
          cell.value = `Tahun Ajaran: ${academicYearName} | Tingkat: ${cls.grade?.grade_name || '-'} | Jurusan: ${cls.major?.major_name || 'Umum'}`;
        }
        cell.font = { italic: true, size: 9.5, color: { argb: 'FF475569' } };
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF1F5F9' } };
        cell.alignment = { vertical: 'middle', horizontal: 'center' };
        cell.border = darkBorderStyle;
      }
      currentRow += 2;

      // 2. Header Tabel Matriks (Hari)
      const headerRow = worksheet.getRow(currentRow);
      headerRow.getCell(1).value = 'JAM / WAKTU';
      headerRow.getCell(1).font = { bold: true, color: { argb: 'FFFFFFFF' }, size: 10 };
      headerRow.getCell(1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF1E293B' } };
      headerRow.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };
      headerRow.getCell(1).border = darkBorderStyle;

      dayLabels.forEach((label, idx) => {
        const cell = headerRow.getCell(idx + 2);
        cell.value = label;
        cell.font = { bold: true, color: { argb: 'FFFFFFFF' }, size: 10 };
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF1E293B' } };
        cell.alignment = { horizontal: 'center', vertical: 'middle' };
        cell.border = darkBorderStyle;
      });
      headerRow.height = 26;
      currentRow++;

      // Filter Profil Slot Waktu Kelas
      const clsSlots = cls.time_slot_profile_id
        ? timeSlots.filter((s) => s.time_slot_profile_id === cls.time_slot_profile_id)
        : timeSlots;

      const periodsSet = new Set<number>();
      clsSlots.forEach((s) => periodsSet.add(s.period_number));
      const periods = Array.from(periodsSet).sort((a, b) => a - b);

      const legendMap = new Map<string, { subjectName: string; teacherName: string; colorHex: string }>();

      // 3. Baris Matriks Jadwal
      periods.forEach((periodNum) => {
        const row = worksheet.getRow(currentRow);
        const sampleSlot = clsSlots.find((s) => s.period_number === periodNum);
        const timeLabel = sampleSlot ? `${sampleSlot.start_time.substring(0, 5)} - ${sampleSlot.end_time.substring(0, 5)}` : '';

        // Kolom 1: Jam & Label Waktu
        const timeCell = row.getCell(1);
        timeCell.value = `Jam ${periodNum}\n${timeLabel}`;
        timeCell.font = { bold: true, size: 8.5, color: { argb: 'FF334155' } };
        timeCell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
        timeCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF1F5F9' } };
        timeCell.border = darkBorderStyle;

        // Kolom 2-7: Hari (Senin - Sabtu)
        days.forEach((day, dayIdx) => {
          const cell = row.getCell(dayIdx + 2);
          const slot = clsSlots.find((s) => s.day.toLowerCase() === day && s.period_number === periodNum);

          if (slot && slot.is_break) {
            cell.value = 'ISTIRAHAT';
            cell.font = { bold: true, size: 8.5, color: { argb: 'FF64748B' } };
            cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFE2E8F0' } };
            cell.alignment = { horizontal: 'center', vertical: 'middle' };
          } else {
            const schedule = slot && matrix[cls.id] ? matrix[cls.id][slot.id] : null;

            if (schedule && schedule.status === 'published') {
              if (schedule.custom_activity) {
                cell.value = `[Rutin]\n${schedule.custom_activity}`;
                cell.font = { bold: true, size: 8.5, color: { argb: 'FF1E293B' } };
                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFEF08A' } };
                cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
              } else if (schedule.class_subject_teacher) {
                const cst = schedule.class_subject_teacher;
                const subjectName = cst.class_subject?.subject?.subject_name || '-';
                const teacherName = cst.teacher?.name || '-';
                const colorCode = cst.class_subject?.subject?.color_code || '#bfdbfe';

                cell.value = `${subjectName}\n${teacherName}`;
                const fgArgb = hexToArgb(colorCode);
                const fontColorArgb = getContrastTextColor(colorCode);

                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: fgArgb } };
                cell.font = { bold: true, size: 8.5, color: { argb: fontColorArgb } };
                cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };

                const key = `${subjectName}_${teacherName}`;
                if (!legendMap.has(key)) {
                  legendMap.set(key, { subjectName, teacherName, colorHex: colorCode });
                }
              }
            } else {
              cell.value = '-';
              cell.font = { color: { argb: 'FFCBD5E1' }, size: 8.5 };
              cell.alignment = { horizontal: 'center', vertical: 'middle' };
            }
          }

          cell.border = darkBorderStyle;
        });

        row.height = 44; // Tinggi baris agar teks 3 baris longgar dan pas di tengah
        currentRow++;
      });

      // 4. Seksi Legenda Warna
      currentRow++;
      const legendTitleRow = worksheet.getRow(currentRow);
      legendTitleRow.getCell(1).value = 'LEGENDA WARNA MATA PELAJARAN & GURU:';
      legendTitleRow.getCell(1).font = { bold: true, size: 9, color: { argb: 'FF475569' } };
      legendTitleRow.getCell(1).alignment = { vertical: 'middle', horizontal: 'left' };
      currentRow++;

      if (legendMap.size > 0) {
        legendMap.forEach((leg) => {
          const legRow = worksheet.getRow(currentRow);
          
          // Sampel Warna Kotak Legenda
          const colorBoxCell = legRow.getCell(1);
          colorBoxCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: hexToArgb(leg.colorHex) } };
          colorBoxCell.border = darkBorderStyle;

          // Keterangan Mapel & Guru
          const labelCell = legRow.getCell(2);
          labelCell.value = `${leg.subjectName} — ${leg.teacherName}`;
          labelCell.font = { size: 8.5, bold: true, color: { argb: 'FF334155' } };
          labelCell.alignment = { vertical: 'middle', horizontal: 'left' };
          worksheet.mergeCells(currentRow, 2, currentRow, 5);

          legRow.height = 20;
          currentRow++;
        });
      } else {
        const emptyLegRow = worksheet.getRow(currentRow);
        emptyLegRow.getCell(1).value = 'Belum ada jadwal mata pelajaran yang dipublikasikan untuk kelas ini.';
        emptyLegRow.getCell(1).font = { italic: true, size: 8.5, color: { argb: 'FF94A3B8' } };
        emptyLegRow.getCell(1).alignment = { vertical: 'middle', horizontal: 'left' };
        currentRow++;
      }

      // 5. Sekat Pemisah Antar Kelas (3 Baris Kosong)
      currentRow += 3;
    });
  });

  // Download Berkas di Browser
  const buffer = await workbook.xlsx.writeBuffer();
  const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
  const url = window.URL.createObjectURL(blob);
  const anchor = document.createElement('a');
  anchor.href = url;
  anchor.download = `Jadwal_Pelajaran_Resmi_${academicYearName.replace(/[^a-zA-Z0-9]/g, '_')}.xlsx`;
  anchor.click();
  window.URL.revokeObjectURL(url);
}