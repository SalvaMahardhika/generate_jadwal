<template>
  <div class="card border rounded-3 p-3 bg-white mb-4">
    <div class="row g-3 align-items-center">
      <div class="col-12 col-md-5">
        <label for="class_select" class="form-label fw-semibold text-dark small mb-1">Pilih Kelas yang Dikelola:</label>
        <select 
          id="class_select" 
          class="form-select form-select-sm bg-light shadow-none" 
          :value="modelValue"
          @change="emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
        >
          <option value="">-- Pilih Kelas --</option>
          <option v-for="cls in classesList" :key="cls.id" :value="cls.id">
            {{ cls.class_name }} - {{ cls.grade?.grade_name || '' }} ({{ cls.academic_year?.name || '' }})
          </option>
        </select>
      </div>

      <div class="col-12 col-md-7" v-if="currentClass">
        <div class="d-flex flex-wrap gap-2 justify-content-md-end">
          <div class="p-2 px-3 bg-light rounded-3 border">
            <small class="text-muted d-block fs-8">Total Terplot</small>
            <span class="fw-bold text-primary small">{{ totalJpAllocated }} JP / Minggu</span>
          </div>
          <div class="p-2 px-3 bg-light rounded-3 border">
            <small class="text-muted d-block fs-8">Jumlah Mapel</small>
            <span class="fw-bold text-dark small">{{ totalSubjects }} Mapel</span>
          </div>
          <div class="p-2 px-3 bg-light rounded-3 border">
            <small class="text-muted d-block fs-8">Wali Kelas</small>
            <span class="fw-bold text-dark small">{{ currentClass.homeroom_teacher?.name || '-' }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { ClassItem } from '../../../../types/class';

defineProps<{
  modelValue: number | string;
  classesList: ClassItem[];
  currentClass: ClassItem | null;
  totalJpAllocated: number;
  totalSubjects: number;
}>();

const emit = defineEmits(['update:modelValue']);
</script>

<style scoped>
.fs-8 {
  font-size: 0.72rem;
}
</style>