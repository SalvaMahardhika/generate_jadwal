<template>
  <SidebarAdmin>
    <div class="container-fluid px-0">
      
      <!-- HEADER HALAMAN -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-2 border-bottom print-hide">
        <div>
          <h5 class="fw-bold text-dark mb-1">Jadwal Pelajaran Resmi</h5>
          <p class="text-secondary small mb-0">Lihat, unduh Excel, dan kelola publikasi jadwal pelajaran yang telah difinalisasi.</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
          <!-- Tombol Hapus Semua Jadwal Resmi -->
          <button 
            type="button" 
            class="btn btn-outline-danger btn-sm rounded-3 px-3 fw-semibold shadow-none d-flex align-items-center gap-1.5"
            @click="showDeletePublishedModal = true"
            :disabled="wizardStore.isProcessing || !wizardStore.selectedAcademicYearId"
          >
            <i class="bi bi-trash3-fill"></i> Hapus Jadwal Resmi
          </button>

          <!-- Tombol Export Excel -->
          <button 
            type="button" 
            class="btn btn-success btn-sm rounded-3 px-3 fw-semibold shadow-none d-flex align-items-center gap-1.5"
            @click="handleExportExcel"
            :disabled="isExporting"
          >
            <span v-if="isExporting" class="spinner-border spinner-border-sm me-1" role="status"></span>
            <i v-else class="bi bi-file-earmark-excel-fill"></i> Export Excel (.xlsx)
          </button>
        </div>
      </div>

      <!-- ALERTS -->
      <div v-if="wizardStore.successMessage" class="alert alert-success border-0 rounded-3 py-2 px-3 small d-flex align-items-center justify-content-between mb-3 print-hide">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill"></i>
          <span>{{ wizardStore.successMessage }}</span>
        </div>
        <button type="button" class="btn-close small" @click="wizardStore.successMessage = null"></button>
      </div>

      <div v-if="wizardStore.errorMessage" class="alert alert-danger border-0 rounded-3 py-2 px-3 small d-flex align-items-center justify-content-between mb-3 print-hide">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <span>{{ wizardStore.errorMessage }}</span>
        </div>
        <button type="button" class="btn-close small" @click="wizardStore.errorMessage = null"></button>
      </div>

      <!-- PANEL KONTROL VIEW & FILTER -->
      <div class="card border rounded-3 p-3 bg-white mb-4 shadow-sm print-hide">
        <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
          
          <!-- MODE VIEW SWITCHER (URUTAN: SEMUA KELAS - PER KELAS - PER GURU) -->
          <div class="btn-group bg-light p-1 rounded-3 border" role="group">
            <button 
              type="button" 
              class="btn btn-sm px-3 fw-semibold border-0 rounded-2"
              :class="wizardStore.viewMode === 'master' ? 'btn-white text-primary shadow-sm' : 'text-secondary'"
              @click="wizardStore.viewMode = 'master'"
            >
              <i class="bi bi-diagram-3 me-1.5"></i> Semua Kelas
            </button>
            <button 
              type="button" 
              class="btn btn-sm px-3 fw-semibold border-0 rounded-2"
              :class="wizardStore.viewMode === 'class' ? 'btn-white text-primary shadow-sm' : 'text-secondary'"
              @click="wizardStore.viewMode = 'class'"
            >
              <i class="bi bi-grid-3x3-gap me-1.5"></i> Per Kelas
            </button>
            <button 
              type="button" 
              class="btn btn-sm px-3 fw-semibold border-0 rounded-2"
              :class="wizardStore.viewMode === 'teacher' ? 'btn-white text-primary shadow-sm' : 'text-secondary'"
              @click="wizardStore.viewMode = 'teacher'"
            >
              <i class="bi bi-person-badge me-1.5"></i> Per Guru
            </button>
          </div>

          <!-- DROPDOWN FILTERS -->
          <div class="d-flex flex-wrap align-items-center gap-2">
            <select 
              class="form-select form-select-sm bg-light border shadow-none" 
              style="min-width: 170px;"
              v-model="wizardStore.selectedAcademicYearId"
            >
              <option v-for="ay in academicYearsList" :key="ay.id" :value="ay.id">
                {{ ay.name }} ({{ ay.semester === 'odd' ? 'Ganjil' : 'Genap' }})
              </option>
            </select>

            <select 
              v-if="wizardStore.viewMode === 'class'"
              class="form-select form-select-sm bg-light border shadow-none" 
              style="min-width: 180px;"
              v-model="wizardStore.selectedClassId"
            >
              <option value="">-- Pilih Kelas --</option>
              <option v-for="cls in classesList" :key="cls.id" :value="cls.id">
                {{ cls.class_name }}
              </option>
            </select>

            <select 
              v-if="wizardStore.viewMode === 'teacher'"
              class="form-select form-select-sm bg-light border shadow-none" 
              style="min-width: 200px;"
              v-model="wizardStore.selectedTeacherId"
            >
              <option value="">-- Pilih Guru --</option>
              <option v-for="t in teachersList" :key="t.id" :value="t.id">
                {{ t.name }}
              </option>
            </select>
          </div>

        </div>
      </div>

      <!-- AREA DOKUMEN MATRIKS READ-ONLY -->
      <div class="printable-paper card border rounded-3 p-4 bg-white shadow-sm">
        
        <!-- HEADER DOKUMEN RESMI (KOP SURAT) -->
        <div class="text-center pb-3 mb-3 border-bottom border-2 border-dark">
          <h4 class="fw-bold text-uppercase mb-1" style="letter-spacing: 1px;">JADWAL PELAJARAN SEKOLAH RESMI</h4>
          <h6 class="fw-bold text-muted mb-0">TAHUN AJARAN {{ currentAcademicYearName }}</h6>
          <small class="text-secondary fw-medium" v-if="wizardStore.viewMode === 'class' && activeClassName">
            KELAS: {{ activeClassName }}
          </small>
          <small class="text-secondary fw-medium" v-else-if="wizardStore.viewMode === 'teacher' && activeTeacherName">
            GURU PENGAMPU: {{ activeTeacherName }}
          </small>
        </div>

        <!-- TAMPILAN MATRIKS PUBLISHED READ-ONLY -->
        <div v-if="wizardStore.viewMode === 'master'">
          <PublishedAllClassesView 
            :classes="wizardStore.masterMatrix?.classes || []"
            :timeSlots="wizardStore.timeSlots"
            :matrix="wizardStore.masterMatrix?.matrix || {}"
            :isLoading="wizardStore.isLoading"
          />
        </div>

        <div v-else-if="wizardStore.viewMode === 'class'">
          <PublishedClassView 
            :timeSlots="wizardStore.timeSlots"
            :schedules="wizardStore.classMatrix?.schedules || {}"
            :isLoading="wizardStore.isLoading"
          />
        </div>

        <div v-else-if="wizardStore.viewMode === 'teacher'">
          <PublishedTeacherView 
            :teacherName="wizardStore.teacherMatrix?.teacher?.name"
            :totalAssignedJp="wizardStore.teacherMatrix?.total_assigned_jp || 0"
            :timeSlots="wizardStore.timeSlots"
            :schedules="wizardStore.teacherMatrix?.schedules || {}"
            :isLoading="wizardStore.isLoading"
          />
        </div>

      </div>

    </div>

    <!-- MODAL KONFIRMASI HAPUS SELURUH JADWAL RESMI -->
    <div 
      v-if="showDeletePublishedModal" 
      class="modal fade show d-block" 
      tabindex="-1" 
      style="background-color: rgba(15, 23, 42, 0.45); backdrop-filter: blur(3px);"
    >
      <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          <div class="modal-body p-4 text-center">
            
            <div class="mb-3">
              <div class="mx-auto rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                <i class="bi bi-trash3-fill fs-2"></i>
              </div>
            </div>

            <h5 class="fw-bold text-dark mb-2">Hapus Semua Jadwal Resmi?</h5>
            <p class="text-secondary small mb-4">
              Seluruh jadwal resmi pada <strong>{{ currentAcademicYearName }}</strong> untuk <strong>semua kelas</strong> (termasuk kegiatan rutin seperti upacara, senam, dll.) akan dihapus bersih dari halaman Jadwal Resmi.
              <br><span class="text-muted mt-1 d-inline-block">(Draft jadwal Anda di halaman Wizard tetap aman dan tidak terhapus).</span>
            </p>

            <div class="d-flex align-items-center justify-content-center gap-2">
              <button 
                type="button" 
                class="btn btn-light rounded-3 px-4 fw-semibold text-secondary shadow-none border"
                @click="showDeletePublishedModal = false"
                :disabled="wizardStore.isProcessing"
              >
                Batal
              </button>
              <button 
                type="button" 
                class="btn btn-danger rounded-3 px-4 fw-semibold shadow-none d-flex align-items-center gap-2"
                @click="executeDeletePublished"
                :disabled="wizardStore.isProcessing"
              >
                <span v-if="wizardStore.isProcessing" class="spinner-border spinner-border-sm" role="status"></span>
                <span>Ya, Hapus Semua</span>
              </button>
            </div>

          </div>
        </div>
      </div>
    </div>
  </SidebarAdmin>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue';
import SidebarAdmin from '../../layouts/SidebarAdmin.vue';

// Import Komponen Read-Only dari folder components/published
import PublishedClassView from './components/published/PublishedClassView.vue';
import PublishedTeacherView from './components/published/PublishedTeacherView.vue';
import PublishedAllClassesView from './components/published/PublishedAllClassesView.vue';

import { useWizardStore } from '../../stores/wizard';
import { WizardService } from '../../services/wizard.service';
import { exportScheduleToExcel } from '../../utils/scheduleExcelExporter';
import api from '../../services/api';

import type { AcademicYear } from '../../types/academicYear';
import type { ClassItem, TeacherOption } from '../../types/class';

const wizardStore = useWizardStore();

const academicYearsList = ref<AcademicYear[]>([]);
const classesList = ref<ClassItem[]>([]);
const teachersList = ref<TeacherOption[]>([]);
const isExporting = ref<boolean>(false);
const showDeletePublishedModal = ref<boolean>(false);

const currentAcademicYearName = computed(() => {
  const ay = academicYearsList.value.find(a => a.id === wizardStore.selectedAcademicYearId);
  return ay ? `${ay.name} (${ay.semester === 'odd' ? 'Semester Ganjil' : 'Semester Genap'})` : '-';
});

const activeClassName = computed(() => {
  const cls = classesList.value.find(c => c.id === wizardStore.selectedClassId);
  return cls ? cls.class_name : '';
});

const activeTeacherName = computed(() => {
  const t = teachersList.value.find(teacher => teacher.id === wizardStore.selectedTeacherId);
  return t ? t.name : '';
});

const fetchDropdowns = async () => {
  try {
    const [ayRes, clsRes, tchrRes] = await Promise.all([
      api.get('/academic-years'),
      api.get('/classes'),
      api.get('/teachers'),
    ]);

    if (ayRes.data?.status === 'success') {
      academicYearsList.value = ayRes.data.data;
      const activeAy = academicYearsList.value.find(a => a.is_active);
      if (activeAy) wizardStore.selectedAcademicYearId = activeAy.id;
      else if (academicYearsList.value.length > 0) wizardStore.selectedAcademicYearId = academicYearsList.value[0].id;
    }

    if (clsRes.data?.status === 'success') {
      classesList.value = clsRes.data.data;
      if (classesList.value.length > 0) wizardStore.selectedClassId = classesList.value[0].id;
    }

    if (tchrRes.data?.status === 'success') {
      teachersList.value = tchrRes.data.data;
      if (teachersList.value.length > 0) wizardStore.selectedTeacherId = teachersList.value[0].id;
    }
  } catch (err) {
    console.error('Gagal memuat dropdown:', err);
  }
};

watch(() => wizardStore.viewMode, () => wizardStore.refreshCurrentView());
watch(() => wizardStore.selectedAcademicYearId, () => wizardStore.refreshCurrentView());
watch(() => wizardStore.selectedClassId, (newId) => {
  if (newId && wizardStore.viewMode === 'class') wizardStore.fetchClassMatrix(newId);
});
watch(() => wizardStore.selectedTeacherId, (newId) => {
  if (newId && wizardStore.viewMode === 'teacher') wizardStore.fetchTeacherMatrix(newId);
});

onMounted(async () => {
  wizardStore.scheduleSource = 'published'; // Pastikan halaman Jadwal Resmi membaca tabel published_schedules
  wizardStore.viewMode = 'master'; // Default awal ke Semua Kelas
  await fetchDropdowns();
  wizardStore.refreshCurrentView();
});

const executeDeletePublished = async () => {
  try {
    await wizardStore.deletePublishedSchedule();
  } finally {
    showDeletePublishedModal.value = false;
  }
};

const handleExportExcel = async () => {
  if (!wizardStore.selectedAcademicYearId) {
    alert('Tahun ajaran belum dipilih.');
    return;
  }

  try {
    isExporting.value = true;
    
    const res = await WizardService.getMasterMatrix(wizardStore.selectedAcademicYearId, 'published');

    if (res.status === 'success' && res.data) {
      await exportScheduleToExcel({
        classes: res.data.classes || classesList.value,
        timeSlots: res.data.time_slots || wizardStore.timeSlots,
        matrix: res.data.matrix || {},
        academicYearName: currentAcademicYearName.value,
      });
    } else {
      alert('Gagal mengambil data jadwal resmi untuk di-export.');
    }
  } catch (err) {
    console.error('Error export Excel:', err);
    alert('Terjadi kesalahan saat memproses file Excel.');
  } finally {
    isExporting.value = false;
  }
};
</script>

<style scoped>
.btn-white {
  background-color: #ffffff !important;
}

.bg-danger-subtle {
  background-color: #fee2e2 !important;
}

@media print {
  body {
    background-color: #ffffff !important;
  }
  .print-hide, 
  nav, 
  aside, 
  .sidebar, 
  header, 
  footer {
    display: none !important;
  }
  .printable-paper {
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
    width: 100% !important;
  }
}
</style>