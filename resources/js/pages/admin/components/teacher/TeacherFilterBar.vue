<template>
  <div class="card border rounded-3 p-3 bg-white mb-3">
    <div class="row g-2 align-items-center">
      <!-- Search Input -->
      <div class="col-12 col-md-6 col-lg-4">
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
          <input 
            type="text" 
            class="form-control bg-light border-start-0 shadow-none" 
            placeholder="Cari nama atau NIP..."
            :value="filters.search"
            @input="emit('update:search', ($event.target as HTMLInputElement).value)"
          >
        </div>
      </div>

      <!-- Status Select -->
      <div class="col-12 col-md-4 col-lg-3">
        <select 
          class="form-select form-select-sm bg-light shadow-none" 
          :value="filters.status"
          @change="emit('update:status', ($event.target as HTMLSelectElement).value)"
        >
          <option value="">Semua Status</option>
          <option value="active">Aktif</option>
          <option value="inactive">Tidak Aktif</option>
        </select>
      </div>

      <!-- Reset Button -->
      <div class="col-12 col-md-2 ms-auto text-end" v-if="filters.search || filters.status">
        <button 
          type="button" 
          class="btn btn-link text-decoration-none text-muted small p-0"
          @click="emit('reset')"
        >
          Reset Filter
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { TeacherFilter } from '../../../../types/teacher';

defineProps<{
  filters: TeacherFilter;
}>();

const emit = defineEmits(['update:search', 'update:status', 'reset']);
</script>