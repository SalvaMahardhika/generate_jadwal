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
              <h6 class="modal-title fw-bold text-dark mb-0">Impor Data Guru dari Excel</h6>
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
                      <th class="py-2 px-3 text-muted fw-semibold fs-8">NIP</th>
                      <th class="py-2 px-3 text-muted fw-semibold fs-8">Nama Guru</th>
                      <th class="py-2 px-3 text-muted fw-semibold fs-8">Gender</th>
                      <th class="py-2 px-3 text-muted fw-semibold fs-8">Max JP</th>
                      <th class="py-2 px-3 text-muted fw-semibold fs-8">Status Keaktifan</th>
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
                      <td class="px-3 fs-8 font-monospace fw-bold text-dark">{{ row.data.nip || '-' }}</td>
                      <td class="px-3 fs-8 fw-medium text-dark">{{ row.data.name || '-' }}</td>
                      <td class="px-3 fs-8">
                        <template v-if="row.data.gender">
                          {{ row.data.gender === 'L' ? 'Laki-Laki' : 'Perempuan' }}
                        </template>
                        <span v-else class="text-muted">-</span>
                      </td>
                      <td class="px-3 fs-8 fw-semibold text-dark">{{ row.data.max_jp_per_week }} JP</td>
                      <td class="px-3 fs-8 text-capitalize">{{ row.data.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}</td>
                      <td class="px-3 fs-8">
                        <span v-if="!row.isValid" class="text-danger fw-medium">
                          <ul class="mb-0 ps-3">
                            <li v-for="(err, eIdx) in row.errors" :key="eIdx">{{ err }}</li>
                          </ul>
                        </span>
                        <span v-else-if="row.isExisting" class="text-warning-emphasis fw-medium">
                          <i class="bi bi-info-circle me-1"></i>NIP sudah terdaftar (Akan dilewati)
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
                <i class="bi bi-cloud-download me-1"></i> Simpan {{ validCount }} Guru Baru
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
import { exportTeacherExcel } from '../../../../utils/teacherExcelExporter';
import type { Teacher, TeacherPayload } from '../../../../types/teacher';

interface ParsedTeacherRow {
  isValid: boolean;
  isExisting: boolean;
  errors: string[];
  data: TeacherPayload;
}

const props = defineProps<{
  show: boolean;
  isSaving: boolean;
  existingTeachers: Teacher[];
}>();

const emit = defineEmits(['close', 'importSuccess']);

const step = ref<'upload' | 'preview'>('upload');
const isDragging = ref(false);
const parseErrorMessage = ref<string | null>(null);
const parsedRows = ref<ParsedTeacherRow[]>([]);

const validCount = computed(() => parsedRows.value.filter(r => r.isValid && !r.isExisting).length);
const skippedCount = computed(() => parsedRows.value.filter(r => r.isValid && r.isExisting).length);
const errorCount = computed(() => parsedRows.value.filter(r => !r.isValid).length);

const downloadTemplate = () => {
  exportTeacherExcel([], true);
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

    const sheet = workbook.getWorksheet('Data_Guru') || workbook.worksheets[0];
    if (!sheet) {
      parseErrorMessage.value = 'Sheet "Data_Guru" tidak ditemukan di dalam berkas Excel.';
      return;
    }

    const rows: ParsedTeacherRow[] = [];
    const seenNipsInFile = new Set<string>();

    const existingNips = new Set(
      (props.existingTeachers || [])
        .filter(t => t.nip)
        .map(t => String(t.nip).trim().toUpperCase())
    );

    sheet.eachRow((row, rowNumber) => {
      if (rowNumber === 1) return; // Skip Header

      const rowValues = row.values as any[];
      if (!rowValues || rowValues.length === 0) return;

      const rawNip = String(rowValues[1] || '').trim();
      const rawName = String(rowValues[2] || '').trim();
      const rawGender = String(rowValues[3] || '').trim();
      const rawMaxJp = rowValues[4];
      const rawStatus = String(rowValues[5] || '').trim();

      // SKIP: Hanya lewati jika seluruh kolom dalam satu baris benar-benar kosong
      const isMaxJpEmpty = rawMaxJp === undefined || rawMaxJp === null || String(rawMaxJp).trim() === '';
      if (!rawNip && !rawName && !rawGender && isMaxJpEmpty && !rawStatus) {
        return;
      }

      const errors: string[] = [];
      const nipClean = rawNip ? rawNip.toUpperCase() : '';

      // Validasi NIP (opsional, tapi jika diisi harus valid)
      if (nipClean) {
        if (nipClean.includes(' ')) {
          errors.push('NIP tidak boleh mengandung spasi.');
        } else if (seenNipsInFile.has(nipClean)) {
          errors.push('NIP duplikat di dalam berkas ini.');
        }
        seenNipsInFile.add(nipClean);
      }

      const isExisting = Boolean(nipClean && existingNips.has(nipClean));

      // Validasi Nama Guru (Wajib diisi)
      if (!rawName) {
        errors.push('Nama Guru wajib diisi.');
      } else if (rawName.length < 2) {
        errors.push('Nama Guru minimal 2 karakter.');
      }

      // Validasi Gender (Wajib diisi)
      let genderClean: 'L' | 'P' | null = null;
      if (!rawGender) {
        errors.push('Jenis kelamin wajib diisi.');
      } else {
        const gLower = rawGender.toLowerCase();
        if (gLower.includes('perempuan') || gLower.includes('wanita') || gLower === 'p') {
          genderClean = 'P';
        } else if (gLower.includes('laki') || gLower.includes('pria') || gLower === 'l') {
          genderClean = 'L';
        } else {
          errors.push('Jenis kelamin harus "Laki-Laki" atau "Perempuan".');
        }
      }

      // Validasi Max JP (Opsional, default: 24)
      let maxJp = 24;
      if (!isMaxJpEmpty) {
        const parsedJp = parseInt(String(rawMaxJp), 10);
        if (isNaN(parsedJp) || parsedJp < 1 || parsedJp > 60) {
          errors.push('Max JP/Minggu harus berupa angka bulat 1 - 60.');
        } else {
          maxJp = parsedJp;
        }
      }

      // Validasi Status (Opsional, default: active)
      const statusClean = rawStatus.toLowerCase().includes('tidak') ? 'inactive' : 'active';

      rows.push({
        isValid: errors.length === 0,
        isExisting,
        errors,
        data: {
          nip: nipClean || null,
          name: rawName,
          gender: (genderClean || 'L') as 'L' | 'P',
          max_jp_per_week: maxJp,
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

.custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
.custom-scroll::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 4px; }
</style>