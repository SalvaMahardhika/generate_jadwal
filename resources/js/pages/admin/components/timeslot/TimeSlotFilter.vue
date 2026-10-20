<template>
  <div class="card border rounded-3 p-3 bg-white mb-4">
    <div class="row g-2 align-items-center">
      <!-- Filter Hari -->
      <div class="col-12 col-md-4">
        <select 
          class="form-select form-select-sm bg-light shadow-none text-capitalize" 
          :value="store.filters.day"
          @change="onDayChange"
        >
          <option value="">Semua Hari</option>
          <option v-for="d in daysList" :key="d" :value="d">{{ d }}</option>
        </select>
      </div>

      <!-- Filter Tipe Slot (JP / Istirahat) -->
      <div class="col-12 col-md-4">
        <select 
          class="form-select form-select-sm bg-light shadow-none" 
          :value="store.filters.is_break"
          @change="onBreakChange"
        >
          <option value="">Semua Tipe Slot</option>
          <option value="0">Jam Pelajaran</option>
          <option value="1">Jam Istirahat / ISHOMA</option>
        </select>
      </div>

      <!-- Reset Filter Button -->
      <div class="col-12 col-md-4 text-md-end" v-if="store.filters.day || store.filters.is_break !== ''">
        <button 
          type="button" 
          class="btn btn-link text-decoration-none text-muted small p-0"
          @click="store.resetFilters()"
        >
          Reset Filter
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useTimeSlotStore } from '../../../../stores/timeSlot';
import type { DayOfWeek } from '../../../../types/timeSlot';

defineProps<{
  daysList: DayOfWeek[];
}>();

const store = useTimeSlotStore();

const onDayChange = (event: Event) => {
  store.filters.day = (event.target as HTMLSelectElement).value as DayOfWeek | '';
  store.fetchTimeSlots();
};

const onBreakChange = (event: Event) => {
  store.filters.is_break = (event.target as HTMLSelectElement).value;
  store.fetchTimeSlots();
};
</script>