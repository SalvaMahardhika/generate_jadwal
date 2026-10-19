<template>
  <SidebarAdmin>
    <div class="container-fluid px-0">
      
      <!-- HEADER HALAMAN (DISEMBUNYIKAN SAAT MENCETAK) -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-2 border-bottom print-hide">
        <div>
          <h5 class="fw-bold text-dark mb-1">Master Jadwal Pelajaran Resmi</h5>
          <p class="text-secondary small mb-0">Lihat, cetak, dan kelola publikasi jadwal pelajaran yang telah difinalisasi.</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
          <!-- Tombol Buka Kembali ke Draft -->
          <button 
            type="button" 
            class="btn btn-outline-warning btn-sm rounded-3 px-3 fw-semibold shadow-none d-flex align-items-center gap-1.5"
            @click="confirmUnpublish"
            :disabled="wizardStore.isProcessing"
          >
            <i class="bi bi-unlock-fill"></i> Buka Kembali ke Draft
          </button>

          <!-- Tombol Cetak / PDF -->
          <button 
            type="button" 
            class="btn btn-primary btn-sm rounded-3 px-3 fw-semibold shadow-none d-flex align-items-center gap-1.5"
            @click="handlePrint"
          >
            <i class="bi bi-printer-fill"></i> Cetak / Export PDF
          </button>
        </div>
      </div>

      <!-- ALERTS (PRINT HIDE) -->
      <div v-if="wizardStore.successMessage" class="alert alert-success border-0 rounded-3 py-2 px-3 small d-flex align-items-center justify-content-between mb-3 print-hide">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill"></i>
          <span>{{ wizardStore.successMessage }}</span>
        </div>
        <button type="button" class="btn-close small" @click="wizardStore.successMessage = null"></button>
      </div>

      <!-- PANEL KONTROL VIEW & FILTER (PRINT HIDE) -->
      <div class="card border rounded-3 p-3 bg-white mb-4 shadow-sm print-hide">
        <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
          
          <!-- MODE VIEW SWITCHER -->
          <div class="btn-group bg-light p-1 rounded-3 border" role="group">
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
            <button 
              type="button" 
              class="btn btn-sm px-3 fw-semibold border-0 rounded-2"
              :class="wizardStore.viewMode === 'master' ? 'btn-white text-primary shadow-sm' : 'text-secondary'"
              @click="wizardStore.viewMode = 'master'"
            >
              <i class="bi bi-diagram-3 me-1.5"></i> Matriks Master
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

      <!-- AREA DOKUMEN CETAK (PRINTABLE AREA) -->
      <div class="printable-paper card border rounded-3 p-4 bg-white shadow-sm">
        
        <!-- HEADER DOKUMEN RESMI (KOP SURAT) -->
        <div class="text-center pb-3 mb-3 border-bottom border-2 border-dark">
          <h4 class="fw-bold text-uppercase mb-1" style="letter-spacing: 1px;">JADWAL PELAJARAN SEKOAH</h4>
          <h6 class="fw-bold text-muted mb-0">TAHUN AJARAN {{ currentAcademicYearName }}</h6>
          <small class="text-secondary fw-medium" v-if="wizardStore.viewMode === 'class' && activeClassName">
            KELAS: {{ activeClassName }}
          </small>
          <small class="text-secondary fw-medium" v-else-if="wizardStore.viewMode === 'teacher' && activeTeacherName">
            GURU PENGAMPU: {{ activeTeacherName }}
          </small>
        </div>

        <!-- TAMPILAN MATRIKS -->
        <div v-if="wizardStore.viewMode === 'class'">
          <ClassMatrixView 
            :timeSlots="wizardStore.timeSlots"
            :schedules="wizardStore.classMatrix?.schedules || {}"
            :isLoading="wizardStore.isLoading"
          />
        </div>

        <div v-else-if="wizardStore.viewMode === 'teacher'">
          <TeacherMatrixView 
            :teacherName="wizardStore.teacherMatrix?.teacher?.name"
            :totalAssignedJp="wizardStore.teacherMatrix?.total_assigned_jp || 0"
            :timeSlots="wizardStore.timeSlots"
            :schedules="wizardStore.teacherMatrix?.schedules || {}"
            :isLoading="wizardStore.isLoading"
          />
        </div>

        <div v-else-if="wizardStore.viewMode === 'master'">
          <MasterMatrixView 
            :classes="wizardStore.masterMatrix?.classes || []"
            :timeSlots="wizardStore.timeSlots"
            :matrix="wizardStore.masterMatrix?.matrix || {}"
            :isLoading="wizardStore.isLoading"
          />
        </div>

        <!-- FOOTER TANDA TANGAN (KHUSUS HANYA SAAT CETAK / DOKUMEN RESMI) -->
        <div class="row mt-5 pt-4 text-center print-only">
          <div class="col-6">
            <p class="mb-5 fw-medium">Mengetahui,<br>Kepala Sekolah</p>
            <p class="fw-bold mb-0 text-decoration-underline">(............................................)</p>
            <small class="text-muted">NIP. ....................................</small>
          </div>
          <div class="col-6">
            <p class="mb-5 fw-medium">Waka Kurikulum</p>
            <p class="fw-bold mb-0 text-decoration-underline">(............................................)</p>
            <small class="text-muted">NIP. ....................................</small>
          </div>
        </div>

      </div>

    </div>
  </SidebarAdmin>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue';
import SidebarAdmin from '../../layouts/SidebarAdmin.vue';
import ClassMatrixView from './components/wizard/ClassMatrixView.vue';
import TeacherMatrixView from './components/wizard/TeacherMatrixView.vue';
import MasterMatrixView from './components/wizard/MasterMatrixView.vue';

import { useWizardStore } from '../../stores/wizard';
import api from '../../services/api';
import type { AcademicYear } from '../../types/academicYear';
import type { ClassItem, TeacherOption } from '../../types/class';

const wizardStore = useWizardStore();

const academicYearsList = ref<AcademicYear[]>([]);
const classesList = ref<ClassItem[]>([]);
const teachersList = ref<TeacherOption[]>([]);

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
  await fetchDropdowns();
  wizardStore.refreshCurrentView();
});

const confirmUnpublish = async () => {
  if (confirm('Apakah Anda yakin ingin mengembalikan jadwal ini menjadi DRAFT? Jadwal akan dapat di-generate atau diedit kembali di halaman Wizard.')) {
    await wizardStore.unpublishSchedule(wizardStore.selectedClassId);
  }
};

const handlePrint = () => {
  window.print();
};
</script>

<style scoped>
.btn-white {
  background-color: #ffffff !important;
}

/* Tampilan Khusus Tanda Tangan hanya muncul saat di-Print */
.print-only {
  display: none !important;
}

/* CSS Khusus Mode Cetak / PDF Export */
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
  .print-only {
    display: flex !important;
  }
}
</style>