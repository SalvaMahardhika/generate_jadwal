<template>
  <div class="d-flex flex-column gap-4">
    
    <!-- PINTAS NAVIGASI KELAS (ANCHOR BADGES) -->
    <div v-if="classes && classes.length > 0" class="card border-0 shadow-sm p-3 bg-white rounded-3">
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="fs-8 fw-bold text-muted text-uppercase me-1">
          <i class="bi bi-compass me-1"></i>Pintas Kelas:
        </span>
        <button 
          v-for="cls in classes" 
          :key="cls.id"
          type="button"
          class="badge bg-light text-primary border border-primary-subtle py-1.5 px-2.5 fs-8 fw-semibold rounded-2 transition-all cursor-pointer shadow-none"
          @click="scrollToClass(cls.id)"
        >
          <i class="bi bi-building me-1 opacity-75"></i>{{ cls.class_name }}
        </button>
      </div>
    </div>

    <!-- LOADING STATE -->
    <div v-if="isLoading" class="card border-0 shadow-sm rounded-3 p-5 text-center bg-white">
      <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
      <span class="text-muted small">Memuat matriks semua kelas...</span>
    </div>

    <!-- EMPTY STATE -->
    <div v-else-if="!classes || classes.length === 0" class="card border-0 shadow-sm rounded-3 p-5 text-center bg-white text-muted small">
      Tidak ada kelas aktif ditemukan.
    </div>

    <!-- LIST OF CLASS SCHEDULE TABLES -->
    <div 
      v-else 
      v-for="cls in classes" 
      :key="cls.id" 
      :id="`class-matrix-${cls.id}`"
      class="card border rounded-3 bg-white overflow-hidden shadow-sm"
    >
      <!-- HEADER KELAS -->
      <div class="card-header bg-light border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <div class="bg-primary text-white rounded-2 px-2.5 py-1 fs-8 fw-bold">
            <i class="bi bi-building me-1"></i>{{ cls.class_name }}
          </div>
          <span class="text-muted fs-8" v-if="cls.grade?.grade_name">Tingkat {{ cls.grade?.grade_name }}</span>
          <span class="text-muted fs-8" v-if="cls.major?.major_name">• {{ cls.major?.major_name }}</span>
        </div>
        <span class="badge bg-secondary-subtle text-secondary fs-8 fw-normal">
          <i class="bi bi-clock me-1"></i>{{ getTimeSlotsForClass(cls).length }} Slot
        </span>
      </div>

      <!-- TABEL MATRIX JADWAL -->
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
            <tr v-for="periodNumber in getPeriodNumbersForClass(cls)" :key="periodNumber">
              <!-- Kolom Waktu -->
              <td class="bg-light fw-bold text-secondary small py-2 px-2 align-middle">
                <div>Jam {{ periodNumber }}</div>
                <small class="text-muted fs-8 font-monospace fw-normal" v-if="getTimeLabel(cls, periodNumber)">
                  {{ getTimeLabel(cls, periodNumber) }}
                </small>
              </td>

              <!-- Kolom Sel Hari -->
              <td 
                v-for="day in days" 
                :key="`${day}-${periodNumber}`"
                class="slot-cell py-2 px-2 align-middle position-relative"
                :class="{ 'bg-break': isBreakSlot(cls, day, periodNumber) }"
                @click="handleCellClick(cls.id, day, periodNumber)"
              >
                <!-- Sel Istirahat -->
                <span v-if="isBreakSlot(cls, day, periodNumber)" class="badge bg-secondary-subtle text-secondary fs-8 fw-normal">
                  ISTIRAHAT
                </span>

                <!-- Sel Berisi Jadwal -->
                <div v-else-if="getSchedule(cls.id, day, periodNumber)" class="schedule-card p-2 rounded-2 text-start position-relative">
                  <!-- Lock Icon -->
                  <i 
                    v-if="getSchedule(cls.id, day, periodNumber)?.is_locked" 
                    class="bi bi-lock-fill position-absolute top-0 end-0 m-1 text-warning fs-8"
                    title="Slot Dikunci"
                  ></i>

                  <!-- Routine Activity -->
                  <div v-if="getSchedule(cls.id, day, periodNumber)?.custom_activity" class="fw-bold text-dark fs-8">
                    <i class="bi bi-flag-fill text-primary me-1"></i>
                    {{ getSchedule(cls.id, day, periodNumber)?.custom_activity }}
                  </div>

                  <!-- Mapel & Guru -->
                  <div v-else-if="getSchedule(cls.id, day, periodNumber)?.class_subject_teacher">
                    <div class="d-flex align-items-center gap-1 mb-0.5">
                      <span 
                        class="color-dot rounded-circle flex-shrink-0"
                        :style="{ backgroundColor: getSchedule(cls.id, day, periodNumber)?.class_subject_teacher?.class_subject?.subject?.color_code || '#cbd5e1' }"
                      ></span>
                      <span class="fw-bold text-dark fs-8 text-truncate">
                        {{ getSchedule(cls.id, day, periodNumber)?.class_subject_teacher?.class_subject?.subject?.subject_name }}
                      </span>
                    </div>
                    <small class="text-muted d-block fs-8 text-truncate">
                      {{ getSchedule(cls.id, day, periodNumber)?.class_subject_teacher?.teacher?.name }}
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

  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { TimeSlot, ScheduleItem } from '../../../../types/wizard';
import type { ClassItem } from '../../../../types/class';

const props = defineProps<{
  classes: ClassItem[];
  timeSlots: TimeSlot[];
  matrix: Record<number, Record<number, ScheduleItem>>;
  isLoading: boolean;
}>();

const emit = defineEmits(['cellClick']);

const days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

const getTimeSlotsForClass = (cls: ClassItem): TimeSlot[] => {
  if (cls.time_slot_profile_id) {
    const filtered = props.timeSlots.filter(s => s.time_slot_profile_id === cls.time_slot_profile_id);
    if (filtered.length > 0) return filtered;
  }
  return props.timeSlots;
};

const getPeriodNumbersForClass = (cls: ClassItem): number[] => {
  const slots = getTimeSlotsForClass(cls);
  const periods = new Set<number>();
  slots.forEach(slot => periods.add(slot.period_number));
  return Array.from(periods).sort((a, b) => a - b);
};

const getSlot = (cls: ClassItem, day: string, periodNumber: number): TimeSlot | undefined => {
  const slots = getTimeSlotsForClass(cls);
  return slots.find(slot => slot.day.toLowerCase() === day.toLowerCase() && slot.period_number === periodNumber);
};

const isBreakSlot = (cls: ClassItem, day: string, periodNumber: number): boolean => {
  const slot = getSlot(cls, day, periodNumber);
  return slot ? slot.is_break : false;
};

const getTimeLabel = (cls: ClassItem, periodNumber: number): string => {
  const slots = getTimeSlotsForClass(cls);
  const slot = slots.find(s => s.period_number === periodNumber);
  if (!slot) return '';
  return `${slot.start_time.substring(0, 5)} - ${slot.end_time.substring(0, 5)}`;
};

const getSchedule = (classId: number, day: string, periodNumber: number): ScheduleItem | null => {
  const cls = props.classes.find(c => c.id === classId);
  if (!cls) return null;
  const slot = getSlot(cls, day, periodNumber);
  if (!slot || !props.matrix || !props.matrix[classId]) return null;
  return props.matrix[classId][slot.id] || null;
};

const handleCellClick = (classId: number, day: string, periodNumber: number) => {
  const cls = props.classes.find(c => c.id === classId);
  if (!cls) return;
  const slot = getSlot(cls, day, periodNumber);
  if (slot && !slot.is_break) {
    emit('cellClick', {
      slot,
      schedule: getSchedule(classId, day, periodNumber),
      classId,
    });
  }
};

const scrollToClass = (classId: number) => {
  const el = document.getElementById(`class-matrix-${classId}`);
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
.cursor-pointer { cursor: pointer; }
.transition-all { transition: all 0.2s ease-in-out; }
</style>