<template>
  <div class="card border rounded-3 bg-white h-100 shadow-sm overflow-hidden">
    <!-- Header Card Hari -->
    <div class="card-header bg-light border-bottom py-3 px-3 d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-calendar-day text-primary"></i>
        <h6 class="fw-bold text-dark text-capitalize mb-0">{{ day }}</h6>
        <span class="badge bg-primary-subtle text-primary rounded-pill fs-8">
          {{ slots.length }} Slot
        </span>
      </div>

      <div class="d-flex align-items-center gap-1">
        <!-- Tombol Hapus Semua Slot Hari Ini -->
        <button 
          v-if="slots.length > 0"
          type="button" 
          class="btn btn-sm btn-light border-0 text-danger p-1 rounded-2"
          title="Hapus Semua Slot Hari Ini"
          @click="emit('clearDay', day)"
        >
          <i class="bi bi-trash-fill"></i>
        </button>

        <!-- Tombol Tambah Slot Hari Ini -->
        <button 
          type="button" 
          class="btn btn-sm btn-light border-0 text-primary p-1 rounded-2"
          title="Tambah Slot untuk Hari Ini"
          @click="emit('addSlot', day)"
        >
          <i class="bi bi-plus-circle-fill"></i>
        </button>
      </div>
    </div>

    <!-- List Slot Waktu Per Hari -->
    <div class="card-body p-0">
      <!-- Empty Day State -->
      <div v-if="slots.length === 0" class="text-center py-4 px-3 text-muted">
        <i class="bi bi-clock fs-4 d-block mb-1 text-secondary opacity-50"></i>
        <small class="d-block mb-2">Belum ada slot waktu</small>
        <button 
          type="button" 
          class="btn btn-outline-primary btn-sm rounded-3 py-1 px-2 fs-8"
          @click="emit('addSlot', day)"
        >
          + Tambah Slot
        </button>
      </div>

      <!-- List Group Slots -->
      <ul v-else class="list-group list-group-flush">
        <li 
          v-for="slot in slots" 
          :key="slot.id" 
          class="list-group-item px-3 py-2.5 d-flex align-items-center justify-content-between slot-item"
          :class="{ 'bg-warning-subtle-light': slot.is_break }"
        >
          <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
            <!-- Badge JP / Istirahat -->
            <span 
              class="badge rounded-2 fw-semibold fs-8 flex-shrink-0"
              :class="slot.is_break ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle'"
            >
              {{ slot.is_break ? 'IST' : `JP ${slot.period_number}` }}
            </span>

            <!-- Detail Jam & Label -->
            <div class="text-truncate">
              <div class="fw-semibold text-dark font-monospace fs-7">
                {{ formatTime(slot.start_time) }} - {{ formatTime(slot.end_time) }}
              </div>
              <small v-if="slot.label" class="text-muted text-truncate d-block fs-8">{{ slot.label }}</small>
            </div>
          </div>

          <!-- Tombol Aksi Slot -->
          <div class="d-flex gap-1 flex-shrink-0">
            <button 
              type="button" 
              class="btn btn-light btn-sm text-secondary border-0 rounded-2 p-1"
              title="Edit"
              @click="emit('editSlot', slot)"
            >
              <i class="bi bi-pencil-square fs-7"></i>
            </button>
            <button 
              type="button" 
              class="btn btn-light btn-sm text-danger border-0 rounded-2 p-1"
              title="Hapus"
              @click="emit('deleteSlot', slot)"
            >
              <i class="bi bi-trash fs-7"></i>
            </button>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { TimeSlot, DayOfWeek } from '../../../../types/timeSlot';

defineProps<{
  day: DayOfWeek;
  slots: TimeSlot[];
}>();

const emit = defineEmits(['addSlot', 'editSlot', 'deleteSlot', 'clearDay']);

const formatTime = (timeStr: string) => {
  if (!timeStr) return '';
  return timeStr.substring(0, 5);
};
</script>

<style scoped>
.bg-primary-subtle {
  background-color: #eff6ff !important;
  color: #2563eb !important;
}
.bg-warning-subtle {
  background-color: #fffbeb !important;
  color: #d97706 !important;
}
.bg-warning-subtle-light {
  background-color: #fffdf5 !important;
}
.slot-item:hover {
  background-color: #f8fafc;
}
.fs-7 {
  font-size: 0.82rem;
}
.fs-8 {
  font-size: 0.72rem;
}
</style>