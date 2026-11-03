import ExcelJS from 'exceljs';
import type { Teacher } from '../types/teacher';

export const exportTeacherExcel = async (
  teachers: Teacher[] = [],
  isTemplateOnly: boolean = false
): Promise<void> => {
  const workbook = new ExcelJS.Workbook();
  workbook.creator = 'Sistem Jadwal Sekolah';
  workbook.lastModifiedBy = 'Sistem Jadwal Sekolah';
  workbook.created = new Date();

  // SHEET 1: Data_Guru
  const sheet1 = workbook.addWorksheet('Data_Guru', {
    views: [{ showGridLines: true }],
  });

  // Set style numFmt: '@' pada kolom NIP agar Excel memperlakukannya sebagai Teks
  sheet1.columns = [
    { header: 'NIP', key: 'nip', width: 24, style: { numFmt: '@' } },
    { header: 'Nama Guru', key: 'name', width: 34 },
    { header: 'Jenis Kelamin', key: 'gender', width: 18 },
    { header: 'Max JP / Minggu', key: 'max_jp_per_week', width: 18 },
    { header: 'Status', key: 'status', width: 15 },
  ];

  const headerRow = sheet1.getRow(1);
  headerRow.height = 26;
  headerRow.eachCell((cell) => {
    cell.fill = {
      type: 'pattern',
      pattern: 'solid',
      fgColor: { argb: 'FF334155' },
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

  if (!isTemplateOnly && teachers.length > 0) {
    teachers.forEach((t) => {
      const row = sheet1.addRow({
        nip: t.nip ? String(t.nip) : '', // Pastikan selalu string
        name: t.name,
        gender: t.gender === 'L' ? 'Laki-Laki' : 'Perempuan',
        max_jp_per_week: t.max_jp_per_week ?? 24,
        status: t.status === 'active' ? 'Aktif' : 'Tidak Aktif',
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
          horizontal: [1, 3, 4, 5].includes(colNumber) ? 'center' : 'left',
        };
      });

      // Paksa format sel NIP (Kolom A) menjadi Teks murni
      row.getCell(1).numFmt = '@';
    });
  }

  const maxRows = Math.max(150, teachers.length + 50);
  for (let r = 2; r <= maxRows; r++) {
    // Set format sel NIP di baris kosong template agar siap diisi sebagai Text
    sheet1.getCell(`A${r}`).numFmt = '@';

    sheet1.getCell(`C${r}`).dataValidation = {
      type: 'list',
      allowBlank: false,
      formulae: ['"Laki-Laki,Perempuan"'],
    };
    sheet1.getCell(`E${r}`).dataValidation = {
      type: 'list',
      allowBlank: true,
      formulae: ['"Aktif,Tidak Aktif"'],
    };
  }

  // SHEET 2: Petunjuk_Pengisian
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

  sheet2.mergeCells('A1:G1');
  const titleCell = sheet2.getCell('A1');
  titleCell.value = 'PETUNJUK PENGISIAN TEMPLATE IMPORT DATA GURU';
  titleCell.font = { name: 'Calibri', size: 12, bold: true, color: { argb: 'FFFFFFFF' } };
  titleCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF1E293B' } };
  titleCell.alignment = { horizontal: 'center', vertical: 'middle' };
  sheet2.getRow(1).height = 32;

  const guideHeaders = ['Kolom', 'Nama Kolom', 'Wajib / Opsional', 'Tipe Data', 'Format / Nilai Valid', 'Contoh', 'Keterangan'];
  sheet2.getRow(3).values = guideHeaders;
  sheet2.getRow(3).height = 24;
  sheet2.getRow(3).eachCell((cell) => {
    cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF475569' } };
    cell.font = { name: 'Calibri', size: 10, bold: true, color: { argb: 'FFFFFFFF' } };
    cell.alignment = { horizontal: 'center', vertical: 'middle' };
  });

  const guideRows = [
    ['A', 'NIP', 'Opsional', 'Teks', 'String (Angka / Karakter)', '198501012010011001', 'Nomor Induk Pegawai atau kode pengenal unik guru.'],
    ['B', 'Nama Guru', 'Wajib', 'Teks', 'String (3-100 Karakter)', 'Ahmad Dahlan, S.Pd.', 'Nama lengkap guru beserta gelar.'],
    ['C', 'Jenis Kelamin', 'Wajib', 'Dropdown', 'Laki-Laki, Perempuan', 'Laki-Laki', 'Jenis kelamin pengajar.'],
    ['D', 'Max JP / Minggu', 'Wajib', 'Angka', 'Integer (1 - 50)', '24', 'Beban mengajar maksimal per minggu.'],
    ['E', 'Status', 'Opsional', 'Dropdown', 'Aktif, Tidak Aktif', 'Aktif', 'Status keaktifan di sistem. Default: Aktif.'],
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

  sheet2.mergeCells('A10:G10');
  const ruleHeader = sheet2.getCell('A10');
  ruleHeader.value = 'ATURAN PENTING PENGISIAN DATA';
  ruleHeader.font = { name: 'Calibri', size: 11, bold: true, color: { argb: 'FFFFFFFF' } };
  ruleHeader.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0F172A' } };
  ruleHeader.alignment = { horizontal: 'left', vertical: 'middle' };
  sheet2.getRow(10).height = 24;

  const rules = [
    '1. Dilarang mengubah, menambah, atau menghapus nama header pada Baris 1 di Sheet "Data_Guru".',
    '2. Isikan data mulai dari Baris 2 pada Sheet "Data_Guru".',
    '3. Jika NIP diisi dan sudah terdaftar di sistem, data tersebut akan dilewati (tidak diimpor ulang/di-overwrite).',
    '4. Gunakan pilihan Dropdown Excel untuk Jenis Kelamin dan Status guna menghindari kesalahan ketik.',
    '5. Pastikan Max JP / Minggu berupa angka bulat positif (misal: 24 atau 28).',
  ];

  rules.forEach((ruleText, idx) => {
    const rowNum = 11 + idx;
    sheet2.mergeCells(`A${rowNum}:G${rowNum}`);
    const c = sheet2.getCell(`A${rowNum}`);
    c.value = ruleText;
    c.font = { name: 'Calibri', size: 10, italic: idx === 0 };
    c.alignment = { vertical: 'middle' };
    sheet2.getRow(rowNum).height = 18;
  });

  const buffer = await workbook.xlsx.writeBuffer();
  const blob = new Blob([buffer], {
    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  const fileName = isTemplateOnly
    ? 'Template_Import_Data_Guru.xlsx'
    : `Export_Data_Guru_${new Date().toISOString().slice(0, 10)}.xlsx`;
  link.download = fileName;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};