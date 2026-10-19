<template>
  <div class="card border rounded-3 bg-white overflow-hidden shadow-sm">
    <div class="table-responsive">
      <table class="table table-bordered align-middle mb-0 text-center text-nowrap">
        <thead class="bg-light">
          <tr>
            <th scope="col" class="py-2.5 px-3 text-muted fw-semibold small" style="width: 110px;">Jam</th>
            <th 
              v-for="day in days" 
              :key="day" 
              scope="col" 
              class="py-2.5 px-3 text-dark fw-bold small text-uppercase"
            >
              {{ day }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading">
            <td :colspan="days.length + 1" class="py-5 text-muted small">
              <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
              Memuat matriks jadwal kelas...
            </td>
          </tr>

          <tr v-else v-for="periodNumber in periodNumbers" :key="periodNumber">
            <!-- Kolom Waktu -->
            <td class="bg-light fw-bold text-secondary small py-2 px-2 align-middle">
              <div>Jam {{ periodNumber }}</div>
              <small class="text-muted fs-8 font-monospace fw-normal" v-if="getTimeLabel(periodNumber)">
                {{ getTimeLabel(periodNumber) }}
              </small>
            </td>

            <!-- Kolom Sel Hari -->
            <td 
              v-for="day in days" 
              :key="`${day}-${periodNumber}`"
              class="slot-cell py-2 px-2 align-middle position-relative"
              :class="{ 'bg-break': isBreakSlot(day, periodNumber) }"
              @click="handleCellClick(day, periodNumber)"
            >
              <!-- Sel Istirahat -->
              <span v-if="isBreakSlot(day, periodNumber)" class="badge bg-secondary-subtle text-secondary fs-8 fw-normal">
                ISTIRAHAT
              </span>

              <!-- Sel Berisi Jadwal -->
              <div v-else-if="getSchedule(day, periodNumber)" class="schedule-card p-2 rounded-2 text-start position-relative">
                <!-- Lock Icon -->
                <i 
                  v-if="getSchedule(day, periodNumber)?.is_locked" 
                  class="bi bi-lock-fill position-absolute top-0 end-0 m-1 text-warning fs-8"
                  title="Slot Dikunci"
                ></i>

                <!-- Routine Activity -->
                <div v-if="getSchedule(day, periodNumber)?.custom_activity" class="fw-bold text-dark fs-8">
                  <i class="bi bi-flag-fill text-primary me-1"></i>
                  {{ getSchedule(day, periodNumber)?.custom_activity }}
                </div>

                <!-- Mapel & Guru -->
                <div v-else-if="getSchedule(day, periodNumber)?.class_subject_teacher">
                  <div class="d-flex align-items-center gap-1 mb-0.5">
                    <span 
                      class="color-dot rounded-circle flex-shrink-0"
                      :style="{ backgroundColor: getSchedule(day, periodNumber)?.class_subject_teacher?.class_subject?.subject?.color_code || '#cbd5e1' }"
                    ></span>
                    <span class="fw-bold text-dark fs-8 text-truncate">
                      {{ getSchedule(day, periodNumber)?.class_subject_teacher?.class_subject?.subject?.subject_name }}
                    </span>
                  </div>
                  <small class="text-muted d-block fs-8 text-truncate">
                    {{ getSchedule(day, periodNumber)?.class_subject_teacher?.teacher?.name }}
                  </small>
                </div>
              </div>

              <!-- Sel Kosong (+ Button) -->
              <button 
                v-else 
                type="button" 
                class="btn btn-link text-decoration-none text-muted btn-add p-0 fs-7"
                title="Tambah Jadwal"
              >
                <i class="bi bi-plus-circle"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { TimeSlot, ScheduleItem } from '../../../../types/wizard';

const props = defineProps<{
  timeSlots: TimeSlot[];
  schedules: Record<number, ScheduleItem>;
  isLoading: boolean;
}>();

const emit = defineEmits(['cellClick']);

const days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

const periodNumbers = computed(() => {
  const periods = new Set<number>();
  props.timeSlots.forEach(slot => periods.add(slot.period_number));
  return Array.from(periods).sort((a, b) => a - b);
});

const getSlot = (day: string, periodNumber: number): TimeSlot | undefined => {
  return props.timeSlots.find(slot => slot.day.toLowerCase() === day.toLowerCase() && slot.period_number === periodNumber);
};

const isBreakSlot = (day: string, periodNumber: number): boolean => {
  const slot = getSlot(day, periodNumber);
  return slot ? slot.is_break : false;
};

const getTimeLabel = (periodNumber: number): string => {
  const slot = props.timeSlots.find(s => s.period_number === periodNumber);
  if (!slot) return '';
  return `${slot.start_time.substring(0, 5)} - ${slot.end_time.substring(0, 5)}`;
};

const getSchedule = (day: string, periodNumber: number): ScheduleItem | null => {
  const slot = getSlot(day, periodNumber);
  if (!slot || !props.schedules) return null;
  return props.schedules[slot.id] || null;
};

const handleCellClick = (day: string, periodNumber: number) => {
  const slot = getSlot(day, periodNumber);
  if (slot && !slot.is_break) {
    emit('cellClick', {
      slot,
      schedule: getSchedule(day, periodNumber)
    });
  }
};
</script>

<style scoped>
.slot-cell {
  height: 65px;
  cursor: pointer;
  transition: background-color 0.15s ease-in-out;
}
.slot-cell:hover:not(.bg-break) {
  background-color: #f8fafc;
}
.bg-break {
  background-color: #f1f5f9 !important;
  cursor: not-allowed;
}
.schedule-card {
  background-color: #eff6ff;
  border: 1px solid #bfdbfe;
}
.color-dot {
  width: 8px;
  height: 8px;
}
.btn-add {
  opacity: 0.3;
}
.slot-cell:hover .btn-add {
  opacity: 1;
  color: #0d6efd !important;
}
.fs-7 { font-size: 0.82rem; }
.fs-8 { font-size: 0.72rem; }
.bg-secondary-subtle { background-color: #e2e8f0 !important; color: #475569 !important; }
</style>