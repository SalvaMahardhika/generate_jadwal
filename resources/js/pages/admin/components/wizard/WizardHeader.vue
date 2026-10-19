<template>
  <div class="card border rounded-3 p-3 bg-white mb-4 shadow-sm">
    <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
      
      <!-- MODE VIEW SWITCHER -->
      <div class="btn-group bg-light p-1 rounded-3 border" role="group">
        <button 
          type="button" 
          class="btn btn-sm px-3 fw-semibold border-0 rounded-2"
          :class="viewMode === 'class' ? 'btn-white text-primary shadow-sm' : 'text-secondary'"
          @click="emit('update:viewMode', 'class')"
        >
          <i class="bi bi-grid-3x3-gap me-1.5"></i> Per Kelas
        </button>
        <button 
          type="button" 
          class="btn btn-sm px-3 fw-semibold border-0 rounded-2"
          :class="viewMode === 'teacher' ? 'btn-white text-primary shadow-sm' : 'text-secondary'"
          @click="emit('update:viewMode', 'teacher')"
        >
          <i class="bi bi-person-badge me-1.5"></i> Per Guru
        </button>
        <button 
          type="button" 
          class="btn btn-sm px-3 fw-semibold border-0 rounded-2"
          :class="viewMode === 'master' ? 'btn-white text-primary shadow-sm' : 'text-secondary'"
          @click="emit('update:viewMode', 'master')"
        >
          <i class="bi bi-diagram-3 me-1.5"></i> Matriks Master
        </button>
      </div>

      <!-- FILTER DROPDOWNS & INPUT MAKS JP -->
      <div class="d-flex flex-wrap align-items-center gap-2">
        <!-- Filter Tahun Ajaran -->
        <select 
          class="form-select form-select-sm bg-light border shadow-none" 
          style="min-width: 170px;"
          :value="selectedAcademicYearId"
          @change="emit('update:selectedAcademicYearId', Number(($event.target as HTMLSelectElement).value))"
        >
          <option v-for="ay in academicYears" :key="ay.id" :value="ay.id">
            {{ ay.name }} ({{ ay.semester === 'odd' ? 'Ganjil' : 'Genap' }})
          </option>
        </select>

        <!-- Filter Kelas (Jika mode == class) -->
        <select 
          v-if="viewMode === 'class'"
          class="form-select form-select-sm bg-light border shadow-none" 
          style="min-width: 150px;"
          :value="selectedClassId"
          @change="emit('update:selectedClassId', Number(($event.target as HTMLSelectElement).value))"
        >
          <option value="">-- Pilih Kelas --</option>
          <option v-for="cls in classes" :key="cls.id" :value="cls.id">
            {{ cls.class_name }}
          </option>
        </select>

        <!-- Filter Guru (Jika mode == teacher) -->
        <select 
          v-if="viewMode === 'teacher'"
          class="form-select form-select-sm bg-light border shadow-none" 
          style="min-width: 180px;"
          :value="selectedTeacherId"
          @change="emit('update:selectedTeacherId', Number(($event.target as HTMLSelectElement).value))"
        >
          <option value="">-- Pilih Guru --</option>
          <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
            {{ teacher.name }}
          </option>
        </select>

        <!-- Input Pilihan Maksimal JP / Hari (Diperbaiki UI-nya agar tidak bentrok) -->
        <div class="d-flex align-items-center bg-light border rounded-3 px-2.5 py-1 gap-2 ms-1" title="Batas Maksimal Jam Pelajaran Per Hari">
          <i class="bi bi-clock-history text-muted fs-8"></i>
          <span class="fs-8 text-secondary fw-medium">Maks JP:</span>
          <select 
            class="form-select form-select-sm border bg-white rounded-2 px-2 py-0 shadow-none fw-bold text-primary cursor-pointer" 
            style="width: 72px; font-size: 0.82rem;"
            :value="maxPeriodNumber"
            @change="emit('update:maxPeriodNumber', Number(($event.target as HTMLSelectElement).value))"
          >
            <option v-for="n in 11" :key="n" :value="n">JP {{ n }}</option>
          </select>
        </div>
      </div>

      <!-- KONTROL AKSI UTAMA -->
      <div class="d-flex flex-wrap gap-2">
        <button 
          type="button" 
          class="btn btn-outline-danger btn-sm rounded-3 px-3 fw-medium d-flex align-items-center gap-1.5 shadow-none"
          @click="emit('reset')"
          :disabled="isProcessing"
        >
          <i class="bi bi-arrow-counterclockwise"></i> Reset
        </button>

        <button 
          type="button" 
          class="btn btn-primary btn-sm rounded-3 px-3 fw-semibold d-flex align-items-center gap-1.5 shadow-none"
          @click="emit('generate')"
          :disabled="isProcessing"
        >
          <span v-if="!isProcessing" class="d-flex align-items-center gap-1.5">
            <i class="bi bi-lightning-charge-fill text-warning"></i> Auto-Generate
          </span>
          <span v-else class="spinner-border spinner-border-sm" role="status"></span>
        </button>

        <button 
          type="button" 
          class="btn btn-success btn-sm rounded-3 px-3 fw-semibold d-flex align-items-center gap-1.5 shadow-none"
          @click="emit('publish')"
          :disabled="isProcessing"
        >
          <i class="bi bi-check2-circle"></i> Publish
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
  maxPeriodNumber: number;
  isProcessing: boolean;
}>();

const emit = defineEmits([
  'update:viewMode', 
  'update:selectedAcademicYearId', 
  'update:selectedClassId', 
  'update:selectedTeacherId',
  'update:maxPeriodNumber',
  'generate',
  'reset',
  'publish'
]);
</script>

<style scoped>
.btn-white {
  background-color: #ffffff !important;
}
.fs-8 {
  font-size: 0.75rem;
}
.cursor-pointer {
  cursor: pointer;
}
</style>