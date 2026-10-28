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

    <!-- DAFTAR TABEL MATRIKS PER KELAS -->
    <div 
      v-else 
      v-for="cls in classes" 
      :key="cls.id" 
      :id="`published-class-matrix-${cls.id}`"
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

      <!-- TABEL KELAS -->
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
            <tr v-for="periodNumber in getPeriodNumbersForClass(cls)" :key="periodNumber">
              <td class="bg-light fw-bold text-secondary small py-2 px-2 align-middle">
                <div>Jam {{ periodNumber }}</div>
                <small class="text-muted fs-8 font-monospace fw-normal" v-if="getTimeLabel(cls, periodNumber)">
                  {{ getTimeLabel(cls, periodNumber) }}
                </small>
              </td>

              <td 
                v-for="day in days" 
                :key="`${day}-${periodNumber}`"
                class="slot-cell py-1 px-1 align-middle"
                :class="{ 'bg-break': isBreakSlot(cls, day, periodNumber) }"
              >
                <span v-if="isBreakSlot(cls, day, periodNumber)" class="badge bg-secondary-subtle text-secondary fs-8 fw-normal">
                  ISTIRAHAT
                </span>

                <template v-else-if="getSchedule(cls.id, day, periodNumber) && getSchedule(cls.id, day, periodNumber)?.status === 'published'">
                  <div 
                    v-if="getSchedule(cls.id, day, periodNumber)?.custom_activity" 
                    class="full-block-tile rounded-2 p-2 h-100 d-flex flex-column justify-content-center align-items-center"
                    style="background-color: #fef08a; color: #854d0e;"
                  >
                    <div class="fw-bold fs-8 text-uppercase text-center">
                      <i class="bi bi-flag-fill me-1"></i>
                      {{ getSchedule(cls.id, day, periodNumber)?.custom_activity }}
                    </div>
                  </div>

                  <div 
                    v-else-if="getSchedule(cls.id, day, periodNumber)?.class_subject_teacher" 
                    class="full-block-tile rounded-2 p-2 h-100 d-flex flex-column justify-content-center align-items-center text-center"
                    :style="getTileStyle(getSchedule(cls.id, day, periodNumber)?.class_subject_teacher?.class_subject?.subject?.color_code)"
                  >
                    <div class="fw-bold fs-8 mb-0.5 text-truncate w-100">
                      {{ getSchedule(cls.id, day, periodNumber)?.class_subject_teacher?.class_subject?.subject?.subject_name }}
                    </div>
                    <small class="opacity-90 fs-8 text-truncate w-100 fw-medium">
                      {{ getSchedule(cls.id, day, periodNumber)?.class_subject_teacher?.teacher?.name }}
                    </small>
                  </div>
                </template>

                <div v-else class="text-muted fs-8">-</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- LEGENDA WARNA DINAMIS DI BAWAH KELAS -->
      <div v-if="getClassLegendItems(cls.id).length > 0" class="card-footer bg-light border-top p-3">
        <h6 class="fs-8 fw-bold text-uppercase text-secondary mb-2">
          <i class="bi bi-palette me-1.5"></i>Legenda Mata Pelajaran & Guru Pengampu:
        </h6>
        <div class="d-flex flex-wrap gap-2">
          <div 
            v-for="(item, idx) in getClassLegendItems(cls.id)" 
            :key="idx"
            class="d-flex align-items-center gap-1.5 bg-white border rounded-2 px-2.5 py-1 shadow-2xs"
          >
            <span class="legend-swatch rounded-circle flex-shrink-0" :style="{ backgroundColor: item.colorHex }"></span>
            <span class="fs-8 fw-semibold text-dark">{{ item.subjectName }}</span>
            <span class="fs-8 text-muted">• {{ item.teacherName }}</span>
            <span class="badge bg-primary-subtle text-primary fs-8 rounded-pill ms-1">{{ item.jpCount }} JP</span>
          </div>
        </div>
      </div>
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

const getContrastTextColor = (hexColor?: string | null): string => {
  if (!hexColor) return '#0f172a';
  let cleanHex = hexColor.replace('#', '').trim();
  if (cleanHex.length === 3) cleanHex = cleanHex.split('').map(c => c + c).join('');
  if (cleanHex.length !== 6) return '#0f172a';

  const r = parseInt(cleanHex.substring(0, 2), 16);
  const g = parseInt(cleanHex.substring(2, 4), 16);
  const b = parseInt(cleanHex.substring(4, 6), 16);
  const yiq = (r * 299 + g * 587 + b * 114) / 1000;
  return yiq >= 150 ? '#0f172a' : '#ffffff';
};

const getTileStyle = (colorCode?: string | null) => {
  const bg = colorCode || '#3b82f6';
  return {
    backgroundColor: bg,
    color: getContrastTextColor(bg),
  };
};

const getClassLegendItems = (classId: number) => {
  const map = new Map<string, { colorHex: string; subjectName: string; teacherName: string; jpCount: number }>();
  const classSchedules = props.matrix[classId] || {};

  Object.values(classSchedules).forEach((sched) => {
    if (sched && sched.status === 'published' && sched.class_subject_teacher) {
      const cst = sched.class_subject_teacher;
      const subjName = cst.class_subject?.subject?.subject_name || '-';
      const tchrName = cst.teacher?.name || '-';
      const color = cst.class_subject?.subject?.color_code || '#3b82f6';

      const key = `${subjName}_${tchrName}`;
      if (map.has(key)) {
        map.get(key)!.jpCount += 1;
      } else {
        map.set(key, { colorHex: color, subjectName: subjName, teacherName: tchrName, jpCount: 1 });
      }
    }
  });

  return Array.from(map.values());
};

const scrollToClass = (classId: number) => {
  const el = document.getElementById(`published-class-matrix-${classId}`);
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
};
</script>

<style scoped>
.slot-cell { height: 62px; width: 14%; }
.bg-break { background-color: #f1f5f9 !important; }
.full-block-tile { box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05); }
.legend-swatch { width: 10px; height: 10px; }
.fs-8 { font-size: 0.72rem; }
.shadow-2xs { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03); }
.cursor-pointer { cursor: pointer; }
.transition-all { transition: all 0.2s ease-in-out; }
</style>