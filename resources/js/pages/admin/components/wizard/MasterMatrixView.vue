<template>
  <div class="card border rounded-3 bg-white overflow-hidden shadow-sm">
    <div class="table-responsive">
      <table class="table table-bordered align-middle mb-0 text-center text-nowrap fs-8">
        <thead class="bg-light">
          <tr>
            <th scope="col" class="py-2.5 px-3 text-muted fw-semibold">Slot Waktu</th>
            <th 
              v-for="cls in classes" 
              :key="cls.id" 
              scope="col" 
              class="py-2.5 px-3 text-dark fw-bold"
            >
              {{ cls.class_name }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading">
            <td :colspan="classes.length + 1" class="py-5 text-muted small">
              <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
              Memuat matriks jadwal master...
            </td>
          </tr>

          <tr v-else v-for="slot in timeSlots" :key="slot.id" :class="{ 'bg-break': slot.is_break }">
            <td class="bg-light fw-bold text-secondary py-2 px-2 text-start">
              {{ capitalize(slot.day) }} (Jam {{ slot.period_number }})
            </td>

            <td v-for="cls in classes" :key="`${cls.id}-${slot.id}`" class="py-2 px-2">
              <span v-if="slot.is_break" class="text-muted opacity-50">ISTIRAHAT</span>

              <div v-else-if="getSchedule(cls.id, slot.id)" class="text-start">
                <div class="fw-bold text-dark text-truncate">
                  {{ getSchedule(cls.id, slot.id)?.class_subject_teacher?.class_subject?.subject?.subject_code || getSchedule(cls.id, slot.id)?.custom_activity }}
                </div>
                <small class="text-muted d-block text-truncate">
                  {{ getSchedule(cls.id, slot.id)?.class_subject_teacher?.teacher?.name }}
                </small>
              </div>

              <span v-else class="text-muted opacity-25">-</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { TimeSlot, ScheduleItem } from '../../../../types/wizard';
import type { ClassItem } from '../../../../types/class';

const props = defineProps<{
  classes: ClassItem[];
  timeSlots: TimeSlot[];
  matrix: Record<number, Record<number, ScheduleItem>>;
  isLoading: boolean;
}>();

const getSchedule = (classId: number, slotId: number): ScheduleItem | null => {
  if (!props.matrix || !props.matrix[classId]) return null;
  return props.matrix[classId][slotId] || null;
};

const capitalize = (str: string) => str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
</script>

<style scoped>
.fs-8 { font-size: 0.72rem; }
.bg-break { background-color: #f1f5f9 !important; }
</style>