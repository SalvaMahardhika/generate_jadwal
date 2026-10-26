<template>
  <div class="card border rounded-3 p-3 bg-white mb-3">
    <div class="row g-2 align-items-center">
      <!-- Input Search -->
      <div class="col-12 col-md-3">
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
          <input 
            type="text" 
            class="form-control bg-light border-start-0 shadow-none" 
            placeholder="Cari kode atau nama mapel..."
            :value="filters.search"
            @input="emit('update:search', ($event.target as HTMLInputElement).value)"
          >
        </div>
      </div>

      <!-- Filter Kategori -->
      <div class="col-12 col-md-3">
        <select 
          class="form-select form-select-sm bg-light shadow-none" 
          :value="filters.category"
          @change="emit('update:category', ($event.target as HTMLSelectElement).value)"
        >
          <option value="">Semua Kategori</option>
          <option value="wajib">Wajib</option>
          <option value="peminatan">Peminatan</option>
          <option value="mulok">Muatan Lokal</option>
          <option value="kejuruan">Kejuruan</option>
          <option value="kokurikuler">Kokurikuler</option>
        </select>
      </div>

      <!-- Filter Tipe Mapel Bersama -->
      <div class="col-12 col-md-2">
        <select 
          class="form-select form-select-sm bg-light shadow-none" 
          :value="filters.is_joint_subject"
          @change="emit('update:is_joint_subject', ($event.target as HTMLSelectElement).value)"
        >
          <option value="">Semua Tipe</option>
          <option value="true">Mapel Bersama</option>
          <option value="false">Mapel Reguler</option>
        </select>
      </div>

      <!-- Filter Status -->
      <div class="col-12 col-md-2">
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

      <!-- Reset Filter Button -->
      <div class="col-12 col-md-2 text-md-end" v-if="filters.search || filters.status || filters.category || filters.is_joint_subject">
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
import type { SubjectFilter } from '../../../../types/subject';

defineProps<{
  filters: SubjectFilter;
}>();

const emit = defineEmits([
  'update:search',
  'update:category',
  'update:status',
  'update:is_joint_subject',
  'reset',
]);
</script>