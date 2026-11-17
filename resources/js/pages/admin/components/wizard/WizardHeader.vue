<template>
  <div class="card border rounded-3 p-3 bg-white mb-4 shadow-sm">
    <div class="d-flex flex-column flex-xxl-row align-items-stretch align-items-xxl-center justify-content-between gap-3">
      
      <!-- GRUP KIRI: MODE VIEW SWITCHER & FILTER -->
      <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-2.5 flex-wrap">
        
        <!-- Mode View Switcher -->
        <div class="btn-group bg-light p-1 rounded-3 border flex-shrink-0" role="group">
          <button 
            type="button" 
            class="btn btn-sm px-3 py-1.5 fw-semibold border-0 rounded-2 text-nowrap d-inline-flex align-items-center justify-content-center gap-1.5"
            :class="viewMode === 'master' ? 'btn-white text-primary shadow-sm' : 'text-secondary'"
            @click="emit('update:viewMode', 'master')"
          >
            <i class="bi bi-grid-3x3-gap-fill"></i>
            <span>Semua Kelas</span>
          </button>
          <button 
            type="button" 
            class="btn btn-sm px-3 py-1.5 fw-semibold border-0 rounded-2 text-nowrap d-inline-flex align-items-center justify-content-center gap-1.5"
            :class="viewMode === 'class' ? 'btn-white text-primary shadow-sm' : 'text-secondary'"
            @click="emit('update:viewMode', 'class')"
          >
            <i class="bi bi-grid-3x3-gap"></i>
            <span>Per Kelas</span>
          </button>
          <button 
            type="button" 
            class="btn btn-sm px-3 py-1.5 fw-semibold border-0 rounded-2 text-nowrap d-inline-flex align-items-center justify-content-center gap-1.5"
            :class="viewMode === 'teacher' ? 'btn-white text-primary shadow-sm' : 'text-secondary'"
            @click="emit('update:viewMode', 'teacher')"
          >
            <i class="bi bi-person-badge"></i>
            <span>Per Guru</span>
          </button>
        </div>

        <!-- Pemisah Vertikal (Desktop) -->
        <div class="vr d-none d-lg-block text-secondary opacity-25 my-1"></div>

        <!-- Filter Dropdowns -->
        <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
          <!-- Filter Tahun Ajaran -->
          <select 
            class="form-select form-select-sm bg-light border rounded-3 py-1.5 px-3 fw-medium text-dark shadow-none" 
            style="min-width: 185px;"
            :value="selectedAcademicYearId"
            @change="emit('update:selectedAcademicYearId', Number(($event.target as HTMLSelectElement).value))"
          >
            <option v-for="ay in academicYears" :key="ay.id" :value="ay.id">
              {{ ay.name }} ({{ ay.semester === 'odd' ? 'Ganjil' : 'Genap' }})
            </option>
          </select>

          <!-- Filter Kelas (Mode Per Kelas) -->
          <select 
            v-if="viewMode === 'class'"
            class="form-select form-select-sm bg-light border rounded-3 py-1.5 px-3 fw-medium text-dark shadow-none" 
            style="min-width: 165px;"
            :value="selectedClassId"
            @change="emit('update:selectedClassId', Number(($event.target as HTMLSelectElement).value))"
          >
            <option value="">-- Pilih Kelas --</option>
            <option v-for="cls in classes" :key="cls.id" :value="cls.id">
              {{ cls.class_name }}
            </option>
          </select>

          <!-- Filter Guru (Mode Per Guru) -->
          <select 
            v-if="viewMode === 'teacher'"
            class="form-select form-select-sm bg-light border rounded-3 py-1.5 px-3 fw-medium text-dark shadow-none" 
            style="min-width: 200px;"
            :value="selectedTeacherId"
            @change="emit('update:selectedTeacherId', Number(($event.target as HTMLSelectElement).value))"
          >
            <option value="">-- Pilih Guru --</option>
            <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
              {{ teacher.name }}
            </option>
          </select>
        </div>
      </div>

      <!-- GRUP KANAN: KONTROL AKSI UTAMA -->
      <div 
        v-if="viewMode !== 'teacher'" 
        class="d-flex flex-wrap align-items-center justify-content-start justify-content-xxl-end gap-2 pt-2 pt-xxl-0 border-top border-xxl-0"
      >
        <button 
          type="button" 
          class="btn btn-outline-danger btn-sm rounded-3 px-3 py-1.5 fw-medium text-nowrap d-inline-flex align-items-center gap-1.5 shadow-none"
          @click="emit('reset')"
          :disabled="isProcessing"
        >
          <i class="bi bi-arrow-counterclockwise"></i>
          <span>{{ viewMode === 'master' ? 'Reset Semua' : 'Reset Kelas' }}</span>
        </button>

        <button 
          type="button" 
          class="btn btn-primary btn-sm rounded-3 px-3 py-1.5 fw-semibold text-nowrap d-inline-flex align-items-center gap-1.5 shadow-none"
          @click="emit('generate')"
          :disabled="isProcessing"
        >
          <span v-if="isProcessing" class="spinner-border spinner-border-sm" role="status"></span>
          <i v-else class="bi bi-lightning-charge-fill text-warning"></i>
          <span>{{ viewMode === 'master' ? 'Generate Semua' : 'Generate Kelas' }}</span>
        </button>

        <button 
          v-if="viewMode === 'master'"
          type="button" 
          class="btn btn-success btn-sm rounded-3 px-3 py-1.5 fw-semibold text-nowrap d-inline-flex align-items-center gap-1.5 shadow-none"
          @click="emit('publish')"
          :disabled="isProcessing"
        >
          <i class="bi bi-check2-circle"></i>
          <span>Publish Jadwal</span>
        </button>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import type { AcademicYear } from '../../../../types/academicYear';
import type { ClassItem, TeacherOption } from '../../../../types/class';
import type { WizardViewMode } from '../../../../stores/wizard';

defineProps<{
  viewMode: WizardViewMode;
  academicYears: AcademicYear[];
  classes: ClassItem[];
  teachers: TeacherOption[];
  selectedAcademicYearId: number | null;
  selectedClassId: number | null;
  selectedTeacherId: number | null;
  isProcessing: boolean;
}>();

const emit = defineEmits([
  'update:viewMode', 
  'update:selectedAcademicYearId', 
  'update:selectedClassId', 
  'update:selectedTeacherId',
  'generate',
  'reset',
  'publish'
]);
</script>

<style scoped>
.btn-white {
  background-color: #ffffff !important;
}
.gap-2\.5 {
  gap: 0.65rem;
}
@media (min-width: 1400px) {
  .border-xxl-0 {
    border-top: 0 !important;
  }
}
</style>