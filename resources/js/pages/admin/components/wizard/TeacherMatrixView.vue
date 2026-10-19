<template>
  <div class="card border rounded-3 bg-white overflow-hidden shadow-sm">
    <div class="card-header bg-light border-bottom p-3 d-flex justify-content-between align-items-center">
      <h6 class="fw-bold text-dark mb-0 fs-7">
        <i class="bi bi-person-badge text-primary me-2"></i> Jadwal Mengajar: {{ teacherName || '-' }}
      </h6>
      <span class="badge bg-primary px-3 py-1.5 rounded-pill fs-8">Total {{ totalAssignedJp }} JP / Minggu</span>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered align-middle mb-0 text-center text-nowrap">
        <thead class="bg-light">
          <tr>
            <th scope="col" class="py-2.5 px-3 text-muted fw-semibold small" style="width: 110px;">Jam</th>
            <th v-for="day in days" :key="day" scope="col" class="py-2.5 px-3 text-dark fw-bold small text-uppercase">
              {{ day }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading">
            <td :colspan="days.length + 1" class="py-5 text-muted small">
              <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
              Memuat data jadwal mengajar guru...
            </td>
          </tr>

          <tr v-else v-for="periodNumber in periodNumbers" :key="periodNumber">
            <td class="bg-light fw-bold text-secondary small py-2 px-2">
              Jam {{ periodNumber }}
            </td>

            <td 
              v-for="day in days" 
              :key="`${day}-${periodNumber}`"
              class="py-2 px-2 align-middle"
              :class="{ 'bg-break': isBreakSlot(day, periodNumber) }"
            >
              <span v-if="isBreakSlot(day, periodNumber)" class="badge bg-secondary-subtle text-secondary fs-8 fw-normal">
                ISTIRAHAT
              </span>

              <div v-else-if="getSchedule(day, periodNumber)" class="p-2 bg-success-subtle border border-success-subtle rounded-2 text-start">
                <span class="fw-bold text-success fs-8 d-block">
                  <i class="bi bi-building me-1"></i> {{ getSchedule(day, periodNumber)?.class?.class_name }}
                </span>
                <small class="text-muted fs-8">
                  {{ getSchedule(day, periodNumber)?.class_subject_teacher?.class_subject?.subject?.subject_name }}
                </small>
              </div>

              <span v-else class="text-muted fs-8 opacity-25">-</span>
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
  teacherName?: string;
  totalAssignedJp: number;
  timeSlots: TimeSlot[];
  schedules: Record<number, ScheduleItem>;
  isLoading: boolean;
}>();

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

const getSchedule = (day: string, periodNumber: number): ScheduleItem | null => {
  const slot = getSlot(day, periodNumber);
  if (!slot || !props.schedules) return null;
  return props.schedules[slot.id] || null;
};
</script>

<style scoped>
.fs-7 { font-size: 0.85rem; }
.fs-8 { font-size: 0.72rem; }
.bg-break { background-color: #f1f5f9 !important; }
.bg-success-subtle { background-color: #f0fdf4 !important; }
.border-success-subtle { border-color: #bbf7d0 !important; }
</style>