<template>
  <Teleport to="body">
    <div v-if="show" class="modal-backdrop fade show"></div>
    <div v-if="show" class="modal fade show d-block" tabindex="-1" @click.self="closeModal">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden bg-white">
          
          <!-- Header -->
          <div class="modal-header border-bottom px-4 py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-file-earmark-excel text-success fs-5"></i>
              <h6 class="modal-title fw-bold text-dark mb-0">Impor Mata Pelajaran dari Excel</h6>
            </div>
            <button type="button" class="btn-close small shadow-none" @click="closeModal" :disabled="isSaving"></button>
          </div>

          <!-- Body -->
          <div class="modal-body p-4">
            
            <!-- STEP 1: Upload File Area -->
            <div v-if="step === 'upload'">
              <div 
                class="upload-dropzone text-center p-5 rounded-4 border-2 border-dashed bg-light transition-all"
                :class="{ 'border-primary bg-primary-subtle': isDragging }"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleDrop"
              >
                <i class="bi bi-cloud-arrow-up text-primary display-4 d-block mb-3"></i>
                <h6 class="fw-bold text-dark mb-1">Tarik & Lepas Berkas Excel di Sini</h6>
                <p class="text-muted small mb-3">Format file yang didukung: <strong>.xlsx</strong> atau <strong>.xls</strong> (Maksimal 5MB)</p>

                <div class="d-flex justify-content-center align-items-center gap-2">
                  <label class="btn btn-primary rounded-3 py-2 px-3 small fw-medium cursor-pointer shadow-none">
                    <i class="bi bi-folder2-open me-1"></i> Pilih Berkas
                    <input type="file" class="d-none" accept=".xlsx, .xls" @change="handleFileSelect" />
                  </label>
                  
                  
                </div>
              </div>

              <div v-if="parseErrorMessage" class="alert alert-danger border-0 rounded-3 py-2 px-3 small text-start mt-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ parseErrorMessage }}
              </div>
            </div>

            <!-- STEP 2: Preview & Validation Results -->
            <div v-else-if="step === 'preview'">
              
              <!-- Summary Bar -->
              <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 p-3 bg-light rounded-3 border mb-3">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded-pill fs-8 fw-semibold">
                    <i class="bi bi-check-circle me-1"></i> {{ validCount }} Baru (Siap Impor)
                  </span>
                  <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1.5 rounded-pill fs-8 fw-semibold" v-if="skippedCount > 0">
                    <i class="bi bi-dash-circle me-1"></i> {{ skippedCount }} Dilewati (Sudah Ada)
                  </span>
                  <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1.5 rounded-pill fs-8 fw-semibold" v-if="errorCount > 0">
                    <i class="bi bi-x-circle me-1"></i> {{ errorCount }} Error
                  </span>
                  <span class="text-muted small ms-1">Total: {{ parsedRows.length }} Baris</span>
                </div>

                <button 
                  type="button" 
                  class="btn btn-link text-decoration-none text-muted small p-0 fw-medium"
                  @click="resetUpload"
                  :disabled="isSaving"
                >
                  <i class="bi bi-arrow-counterclockwise me-1"></i> Pilih Berkas Lain
                </button>
              </div>

              <!-- Preview Table -->
              <div class="table-responsive rounded-3 border custom-scroll" style="max-height: 380px;">
                <table class="table table-sm table-hover align-middle mb-0">
                  <thead class="bg-light sticky-top">
                    <tr>
                      <th class="py-2 px-3 text-muted fw-semibold fs-8" style="width: 40px;">#</th>
                      <th class="py-2 px-3 text-muted fw-semibold fs-8">Status</th>
                      <th class="py-2 px-3 text-muted fw-semibold fs-8">Kode</th>
                      <th class="py-2 px-3 text-muted fw-semibold fs-8">Nama Mapel</th>
                      <th class="py-2 px-3 text-muted fw-semibold fs-8">Kategori</th>
                      <th class="py-2 px-3 text-muted fw-semibold fs-8">Tipe</th>
                      <th class="py-2 px-3 text-muted fw-semibold fs-8">KKM</th>
                      <th class="py-2 px-3 text-muted fw-semibold fs-8">Keterangan Validasi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr 
                      v-for="(row, idx) in parsedRows" 
                      :key="idx"
                      :class="{ 
                        'table-danger-subtle': !row.isValid,
                        'table-warning-subtle': row.isValid && row.isExisting
                      }"
                    >
                      <td class="px-3 fs-8 text-secondary">{{ idx + 1 }}</td>
                      <td class="px-3 fs-8">
                        <span v-if="!row.isValid" class="badge bg-danger text-white fs-9 fw-semibold">Error</span>
                        <span v-else-if="row.isExisting" class="badge bg-warning text-dark fs-9 fw-semibold">Sudah Ada</span>
                        <span v-else class="badge bg-success-subtle text-success fs-9 fw-semibold">Siap Impor</span>
                      </td>
                      <td class="px-3 fs-8 font-monospace fw-bold text-dark">{{ row.data.subject_code || '-' }}</td>
                      <td class="px-3 fs-8 fw-medium text-dark">{{ row.data.subject_name || '-' }}</td>
                      <td class="px-3 fs-8 text-capitalize">{{ row.data.category || '-' }}</td>
                      <td class="px-3 fs-8">
                        <span v-if="row.data.is_joint_subject" class="badge bg-info-subtle text-info border border-info-subtle fs-9">Mapel Bersama</span>
                        <span v-else class="text-muted">Reguler</span>
                      </td>
                      <td class="px-3 fs-8 fw-semibold text-dark">{{ row.data.passing_grade ?? 75 }}</td>
                      <td class="px-3 fs-8">
                        <span v-if="!row.isValid" class="text-danger fw-medium">
                          <ul class="mb-0 ps-3">
                            <li v-for="(err, eIdx) in row.errors" :key="eIdx">{{ err }}</li>
                          </ul>
                        </span>
                        <span v-else-if="row.isExisting" class="text-warning-emphasis fw-medium">
                          <i class="bi bi-info-circle me-1"></i>Sudah terdaftar di sistem (Akan dilewati)
                        </span>
                        <span v-else class="text-success fw-medium">
                          <i class="bi bi-check-lg me-1"></i>Siap diimpor
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

            </div>

          </div>

          <!-- Footer -->
          <div class="modal-footer border-top px-4 py-3 bg-light">
            <button 
              type="button" 
              class="btn btn-light rounded-3 py-2 px-3 small fw-medium border shadow-none"
              @click="closeModal"
              :disabled="isSaving"
            >
              Batal
            </button>

            <button 
              v-if="step === 'preview'"
              type="button" 
              class="btn btn-primary rounded-3 py-2 px-3 small fw-semibold shadow-none d-flex align-items-center gap-2"
              @click="submitImport"
              :disabled="isSaving || validCount === 0"
            >
              <span v-if="!isSaving">
                <i class="bi bi-cloud-download me-1"></i> Simpan {{ validCount }} Mapel Baru
              </span>
              <span v-else class="d-flex align-items-center gap-1">
                <span class="spinner-border spinner-border-sm" role="status"></span>
                <span>Memproses...</span>
              </span>
            </button>
          </div>

        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import ExcelJS from 'exceljs';
import { exportSubjectExcel } from '../../../../utils/subjectExcelExporter';
import type { Subject, SubjectPayload } from '../../../../types/subject';

interface ParsedSubjectRow {
  isValid: boolean;
  isExisting: boolean;
  errors: string[];
  data: SubjectPayload;
}

const props = defineProps<{
  show: boolean;
  isSaving: boolean;
  existingSubjects: Subject[];
}>();

const emit = defineEmits(['close', 'importSuccess']);

const step = ref<'upload' | 'preview'>('upload');
const isDragging = ref(false);
const parseErrorMessage = ref<string | null>(null);
const parsedRows = ref<ParsedSubjectRow[]>([]);

// Palette warna otomatis yang serasi dan kontras
const defaultColors = [
  '#2563EB', '#D97706', '#059669', '#DC2626', '#7C3AED', 
  '#DB2777', '#0284C7', '#4D7C0F', '#475569', '#9333EA',
  '#0891B2', '#CA8A04'
];

const validCount = computed(() => parsedRows.value.filter(r => r.isValid && !r.isExisting).length);
const skippedCount = computed(() => parsedRows.value.filter(r => r.isValid && r.isExisting).length);
const errorCount = computed(() => parsedRows.value.filter(r => !r.isValid).length);

const downloadTemplate = () => {
  exportSubjectExcel([], true);
};

const resetUpload = () => {
  step.value = 'upload';
  parsedRows.value = [];
  parseErrorMessage.value = null;
};

const closeModal = () => {
  if (!props.isSaving) {
    resetUpload();
    emit('close');
  }
};

const handleDrop = (e: DragEvent) => {
  isDragging.value = false;
  const files = e.dataTransfer?.files;
  if (files && files.length > 0) {
    processExcelFile(files[0]);
  }
};

const handleFileSelect = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    processExcelFile(target.files[0]);
  }
};

const categoryMapFromHuman: Record<string, string> = {
  wajib: 'wajib',
  peminatan: 'peminatan',
  'muatan lokal': 'mulok',
  mulok: 'mulok',
  kejuruan: 'kejuruan',
  kokurikuler: 'kokurikuler',
};

const processExcelFile = async (file: File) => {
  parseErrorMessage.value = null;

  if (!file.name.match(/\.(xlsx|xls)$/i)) {
    parseErrorMessage.value = 'Berkas harus berupa file Excel (.xlsx atau .xls).';
    return;
  }

  try {
    const arrayBuffer = await file.arrayBuffer();
    const workbook = new ExcelJS.Workbook();
    await workbook.xlsx.load(arrayBuffer);

    const sheet = workbook.getWorksheet('Data_Mapel') || workbook.worksheets[0];
    if (!sheet) {
      parseErrorMessage.value = 'Sheet "Data_Mapel" tidak ditemukan di dalam berkas Excel.';
      return;
    }

    const rows: ParsedSubjectRow[] = [];
    const seenCodesInFile = new Set<string>();

    // Map kode mapel yang sudah tersimpan di database/store
    const existingCodes = new Set(
      (props.existingSubjects || []).map(s => s.subject_code.toUpperCase())
    );

    sheet.eachRow((row, rowNumber) => {
      if (rowNumber === 1) return; // Skip Header

      const rowValues = row.values as any[];
      if (!rowValues || rowValues.length === 0) return;

      const rawCode = String(rowValues[1] || '').trim();
      const rawName = String(rowValues[2] || '').trim();
      const rawCategory = String(rowValues[3] || '').trim();
      const rawType = String(rowValues[4] || '').trim();
      const rawGrade = rowValues[5];
      const rawStatus = String(rowValues[6] || '').trim();

      // Skip baris kosong
      if (!rawCode && !rawName) return;

      const errors: string[] = [];
      const codeClean = rawCode.toUpperCase();

      // Validasi Kode Mapel
      if (!codeClean) {
        errors.push('Kode Mapel wajib diisi.');
      } else if (codeClean.includes(' ')) {
        errors.push('Kode Mapel tidak boleh mengandung spasi.');
      } else if (codeClean.length < 2 || codeClean.length > 15) {
        errors.push('Kode Mapel harus 2 - 15 karakter.');
      } else if (seenCodesInFile.has(codeClean)) {
        errors.push('Kode Mapel duplikat di dalam berkas ini.');
      }
      if (codeClean) seenCodesInFile.add(codeClean);

      // Cek apakah kode mapel sudah ada di sistem
      const isExisting = existingCodes.has(codeClean);

      // Validasi Nama Mapel
      if (!rawName) {
        errors.push('Nama Mata Pelajaran wajib diisi.');
      }

      // Validasi Kategori
      const categoryClean = categoryMapFromHuman[rawCategory.toLowerCase()] || 'wajib';

      // Validasi Tipe Pelaksanaan
      const isJointSubject = rawType.toLowerCase().includes('bersama');

      // Validasi KKM
      let passingGrade = 75;
      if (rawGrade !== undefined && rawGrade !== null && rawGrade !== '') {
        const parsedGrade = parseInt(String(rawGrade), 10);
        if (isNaN(parsedGrade) || parsedGrade < 0 || parsedGrade > 100) {
          errors.push('KKM harus berupa angka bulat antara 0 - 100.');
        } else {
          passingGrade = parsedGrade;
        }
      }

      // Penentuan warna otomatis dari palette
      const colorCode = defaultColors[rows.length % defaultColors.length];

      // Validasi Status
      const statusClean = rawStatus.toLowerCase().includes('tidak') ? 'inactive' : 'active';

      rows.push({
        isValid: errors.length === 0,
        isExisting,
        errors,
        data: {
          subject_code: codeClean,
          subject_name: rawName,
          category: categoryClean,
          is_joint_subject: isJointSubject,
          passing_grade: passingGrade,
          color_code: colorCode,
          status: statusClean,
        },
      });
    });

    if (rows.length === 0) {
      parseErrorMessage.value = 'Tidak ada data baris yang terdeteksi di Sheet Excel.';
      return;
    }

    parsedRows.value = rows;
    step.value = 'preview';
  } catch (err) {
    parseErrorMessage.value = 'Gagal membaca berkas Excel. Pastikan format berkas valid.';
  }
};

const submitImport = () => {
  // Hanya ambil mapel yang valid dan belum ada di sistem
  const validPayloads = parsedRows.value
    .filter(r => r.isValid && !r.isExisting)
    .map(r => r.data);

  if (validPayloads.length === 0) return;
  emit('importSuccess', validPayloads);
};
</script>

<style scoped>
.fs-8 { font-size: 0.75rem; }
.fs-9 { font-size: 0.68rem; }
.cursor-pointer { cursor: pointer; }
.upload-dropzone { border-color: #cbd5e1; }
.table-danger-subtle { background-color: #fef2f2 !important; }
.table-warning-subtle { background-color: #fffbeb !important; }
.bg-success-subtle { background-color: #f0fdf4 !important; color: #16a34a !important; }
.bg-warning-subtle { background-color: #fffbeb !important; color: #b45309 !important; }
.bg-danger-subtle { background-color: #fef2f2 !important; color: #dc2626 !important; }
.bg-info-subtle { background-color: #e0f2fe !important; color: #0284c7 !important; }

.custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
.custom-scroll::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 4px; }
</style>