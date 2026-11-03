import ExcelJS from 'exceljs';
import type { Subject } from '../types/subject';

export const exportSubjectExcel = async (
  subjects: Subject[] = [],
  isTemplateOnly: boolean = false
): Promise<void> => {
  const workbook = new ExcelJS.Workbook();
  workbook.creator = 'Sistem Jadwal Sekolah';
  workbook.lastModifiedBy = 'Sistem Jadwal Sekolah';
  workbook.created = new Date();

  // -------------------------------------------------------------
  // SHEET 1: Data_Mapel (Tanpa Kolom Warna)
  // -------------------------------------------------------------
  const sheet1 = workbook.addWorksheet('Data_Mapel', {
    views: [{ showGridLines: true }],
  });

  sheet1.columns = [
    { header: 'Kode Mapel', key: 'subject_code', width: 16 },
    { header: 'Nama Mata Pelajaran', key: 'subject_name', width: 32 },
    { header: 'Kategori', key: 'category', width: 20 },
    { header: 'Tipe Pelaksanaan', key: 'is_joint_subject', width: 20 },
    { header: 'KKM', key: 'passing_grade', width: 12 },
    { header: 'Status', key: 'status', width: 15 },
  ];

  // Format Header Sheet 1
  const headerRow = sheet1.getRow(1);
  headerRow.height = 26;
  headerRow.eachCell((cell) => {
    cell.fill = {
      type: 'pattern',
      pattern: 'solid',
      fgColor: { argb: 'FF334155' }, // Slate 700
    };
    cell.font = {
      name: 'Calibri',
      size: 11,
      bold: true,
      color: { argb: 'FFFFFFFF' },
    };
    cell.alignment = { horizontal: 'center', vertical: 'middle' };
    cell.border = {
      top: { style: 'thin', color: { argb: 'FF94A3B8' } },
      left: { style: 'thin', color: { argb: 'FF94A3B8' } },
      bottom: { style: 'medium', color: { argb: 'FF1E293B' } },
      right: { style: 'thin', color: { argb: 'FF94A3B8' } },
    };
  });

  const categoryMapToHuman: Record<string, string> = {
    wajib: 'Wajib',
    peminatan: 'Peminatan',
    mulok: 'Muatan Lokal',
    kejuruan: 'Kejuruan',
    kokurikuler: 'Kokurikuler',
  };

  // Isi Data jika bukan template only
  if (!isTemplateOnly && subjects.length > 0) {
    subjects.forEach((subj) => {
      const row = sheet1.addRow({
        subject_code: subj.subject_code,
        subject_name: subj.subject_name,
        category: subj.category ? categoryMapToHuman[subj.category] || subj.category : 'Wajib',
        is_joint_subject: subj.is_joint_subject ? 'Mapel Bersama' : 'Reguler',
        passing_grade: subj.passing_grade ?? 75,
        status: subj.status === 'active' ? 'Aktif' : 'Tidak Aktif',
      });

      row.height = 22;
      row.eachCell((cell, colNumber) => {
        cell.font = { name: 'Calibri', size: 11 };
        cell.border = {
          top: { style: 'thin', color: { argb: 'FFE2E8F0' } },
          left: { style: 'thin', color: { argb: 'FFE2E8F0' } },
          bottom: { style: 'thin', color: { argb: 'FFE2E8F0' } },
          right: { style: 'thin', color: { argb: 'FFE2E8F0' } },
        };
        cell.alignment = {
          vertical: 'middle',
          horizontal: [1, 3, 4, 5, 6].includes(colNumber) ? 'center' : 'left',
        };
      });
    });
  }

  // Set Data Validation Dropdowns (Baris 2 s.d 150)
  const maxRows = Math.max(150, subjects.length + 50);
  for (let r = 2; r <= maxRows; r++) {
    sheet1.getCell(`C${r}`).dataValidation = {
      type: 'list',
      allowBlank: false,
      formulae: ['"Wajib,Peminatan,Muatan Lokal,Kejuruan,Kokurikuler"'],
    };
    sheet1.getCell(`D${r}`).dataValidation = {
      type: 'list',
      allowBlank: false,
      formulae: ['"Reguler,Mapel Bersama"'],
    };
    sheet1.getCell(`F${r}`).dataValidation = {
      type: 'list',
      allowBlank: true,
      formulae: ['"Aktif,Tidak Aktif"'],
    };
  }

  // -------------------------------------------------------------
  // SHEET 2: Petunjuk_Pengisian
  // -------------------------------------------------------------
  const sheet2 = workbook.addWorksheet('Petunjuk_Pengisian', {
    views: [{ showGridLines: true }],
  });

  sheet2.columns = [
    { width: 10 },
    { width: 24 },
    { width: 16 },
    { width: 14 },
    { width: 35 },
    { width: 16 },
    { width: 45 },
  ];

  // Banner Title
  sheet2.mergeCells('A1:G1');
  const titleCell = sheet2.getCell('A1');
  titleCell.value = 'PETUNJUK PENGISIAN TEMPLATE IMPORT DATA MATA PELAJARAN';
  titleCell.font = { name: 'Calibri', size: 12, bold: true, color: { argb: 'FFFFFFFF' } };
  titleCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF1E293B' } };
  titleCell.alignment = { horizontal: 'center', vertical: 'middle' };
  sheet2.getRow(1).height = 32;

  // Header Tabel Panduan
  const guideHeaders = ['Kolom', 'Nama Kolom', 'Wajib / Opsional', 'Tipe Data', 'Format / Nilai Valid', 'Contoh', 'Keterangan'];
  sheet2.getRow(3).values = guideHeaders;
  sheet2.getRow(3).height = 24;
  sheet2.getRow(3).eachCell((cell) => {
    cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF475569' } };
    cell.font = { name: 'Calibri', size: 10, bold: true, color: { argb: 'FFFFFFFF' } };
    cell.alignment = { horizontal: 'center', vertical: 'middle' };
  });

  const guideRows = [
    ['A', 'Kode Mapel', 'Wajib', 'Teks', 'String (3-10 Karakter, Kapital, Tanpa Spasi)', 'MTK', 'Identitas unik mapel. Tidak boleh ada duplikat.'],
    ['B', 'Nama Mata Pelajaran', 'Wajib', 'Teks', 'String (3-100 Karakter)', 'Matematika', 'Nama resmi mata pelajaran.'],
    ['C', 'Kategori', 'Wajib', 'Dropdown', 'Wajib, Peminatan, Muatan Lokal, Kejuruan, Kokurikuler', 'Wajib', 'Kategori kurikulum mata pelajaran.'],
    ['D', 'Tipe Pelaksanaan', 'Wajib', 'Dropdown', 'Reguler, Mapel Bersama', 'Reguler', 'Reguler (KBM kelas biasa) atau Mapel Bersama (misal: P5).'],
    ['E', 'KKM', 'Wajib', 'Angka', 'Integer (0 - 100)', '75', 'Nilai Ketuntasan Minimal.'],
    ['F', 'Status', 'Opsional', 'Dropdown', 'Aktif, Tidak Aktif', 'Aktif', 'Status keaktifan mapel di sistem. Default: Aktif.'],
  ];

  guideRows.forEach((gRow, idx) => {
    const row = sheet2.getRow(4 + idx);
    row.values = gRow;
    row.height = 20;
    row.eachCell((cell, colNum) => {
      cell.font = { name: 'Calibri', size: 10 };
      cell.border = {
        top: { style: 'thin', color: { argb: 'FFE2E8F0' } },
        left: { style: 'thin', color: { argb: 'FFE2E8F0' } },
        bottom: { style: 'thin', color: { argb: 'FFE2E8F0' } },
        right: { style: 'thin', color: { argb: 'FFE2E8F0' } },
      };
      if (colNum === 1 || colNum === 3 || colNum === 4 || colNum === 6) {
        cell.alignment = { horizontal: 'center', vertical: 'middle' };
      } else {
        cell.alignment = { vertical: 'middle' };
      }
    });
  });

  // Section Aturan
  sheet2.mergeCells('A11:G11');
  const ruleHeader = sheet2.getCell('A11');
  ruleHeader.value = 'ATURAN PENTING PENGISIAN DATA';
  ruleHeader.font = { name: 'Calibri', size: 11, bold: true, color: { argb: 'FFFFFFFF' } };
  ruleHeader.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0F172A' } };
  ruleHeader.alignment = { horizontal: 'left', vertical: 'middle' };
  sheet2.getRow(11).height = 24;

  const rules = [
    '1. Dilarang mengubah, menambah, atau menghapus nama header pada Baris 1 di Sheet "Data_Mapel".',
    '2. Isikan data mulai dari Baris 2 pada Sheet "Data_Mapel".',
    '3. Kode Mapel harus unik. Jika Kode Mapel sudah terdaftar di sistem, data tersebut akan dilewati (tidak diimpor ulang/di-overwrite).',
    '4. Kolom bersimbol Dropdown sebaiknya diisi menggunakan pilihan dropdown yang tersedia untuk menghindari kesalahan ketik.',
    '5. Pastikan nilai KKM berupa angka bulat (integer) antara 0 sampai 100.',
    '6. Warna label jadwal akan secara otomatis ditentukan oleh sistem.',
  ];

  rules.forEach((ruleText, idx) => {
    const rowNum = 12 + idx;
    sheet2.mergeCells(`A${rowNum}:G${rowNum}`);
    const c = sheet2.getCell(`A${rowNum}`);
    c.value = ruleText;
    c.font = { name: 'Calibri', size: 10, italic: idx === 0 };
    c.alignment = { vertical: 'middle' };
    sheet2.getRow(rowNum).height = 18;
  });

  // Process File Download
  const buffer = await workbook.xlsx.writeBuffer();
  const blob = new Blob([buffer], {
    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  const fileName = isTemplateOnly
    ? 'Template_Import_Mata_Pelajaran.xlsx'
    : `Export_Mata_Pelajaran_${new Date().toISOString().slice(0, 10)}.xlsx`;
  link.download = fileName;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};