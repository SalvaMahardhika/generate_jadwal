<template>
  <div class="card border rounded-3 bg-white overflow-hidden shadow-sm">
    <!-- LOADING STATE -->
    <div v-if="isLoading" class="p-5 text-center text-muted small">
      <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
      Memuat jadwal kelas...
    </div>

    <template v-else>
      <!-- TABEL MATRIX JADWAL PER KELAS -->
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
            <tr v-for="periodNumber in periodNumbers" :key="periodNumber">
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
                class="slot-cell py-1 px-1 align-middle"
                :class="{ 'bg-break': isBreakSlot(day, periodNumber) }"
              >
                <!-- Sel Istirahat -->
                <span v-if="isBreakSlot(day, periodNumber)" class="badge bg-secondary-subtle text-secondary fs-8 fw-normal">
                  ISTIRAHAT
                </span>

                <!-- Sel Berisi Jadwal Terpublikasi -->
                <template v-else-if="getSchedule(day, periodNumber) && getSchedule(day, periodNumber)?.status === 'published'">
                  <!-- Routine Activity (Warna Kuning Murni) -->
                  <div 
                    v-if="getSchedule(day, periodNumber)?.custom_activity" 
                    class="full-block-tile rounded-2 p-2 h-100 d-flex flex-column justify-content-center align-items-center"
                    style="background-color: #fef08a; color: #854d0e;"
                  >
                    <div class="fw-bold fs-8 text-uppercase text-center">
                      <i class="bi bi-flag-fill me-1"></i>
                      {{ getSchedule(day, periodNumber)?.custom_activity }}
                    </div>
                  </div>

                  <!-- Mata Pelajaran & Guru (Full Block Color) -->
                  <div 
                    v-else-if="getSchedule(day, periodNumber)?.class_subject_teacher" 
                    class="full-block-tile rounded-2 p-2 h-100 d-flex flex-column justify-content-center align-items-center text-center"
                    :style="getTileStyle(getSchedule(day, periodNumber)?.class_subject_teacher?.class_subject?.subject?.color_code)"
                  >
                    <div class="fw-bold fs-8 mb-0.5 text-truncate w-100">
                      {{ getSchedule(day, periodNumber)?.class_subject_teacher?.class_subject?.subject?.subject_name }}
                    </div>
                    <small class="opacity-90 fs-8 text-truncate w-100 fw-medium">
                      {{ getSchedule(day, periodNumber)?.class_subject_teacher?.teacher?.name }}
                    </small>
                  </div>
                </template>

                <!-- Slot Kosong -->
                <div v-else class="text-muted fs-8">-</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- LEGENDA WARNA DINAMIS DI BAWAH TABEL -->
      <div v-if="legendItems.length > 0" class="card-footer bg-light border-top p-3">
        <h6 class="fs-8 fw-bold text-uppercase text-secondary mb-2">
          <i class="bi bi-palette me-1.5"></i>Legenda Mata Pelajaran & Guru Pengampu:
        </h6>
        <div class="d-flex flex-wrap gap-2">
          <div 
            v-for="(item, idx) in legendItems" 
            :key="idx"
            class="d-flex align-items-center gap-1.5 bg-white border rounded-2 px-2.5 py-1 shadow-2xs"
          >
            <span 
              class="legend-swatch rounded-circle flex-shrink-0"
              :style="{ backgroundColor: item.colorHex }"
            ></span>
            <span class="fs-8 fw-semibold text-dark">{{ item.subjectName }}</span>
            <span class="fs-8 text-muted">• {{ item.teacherName }}</span>
            <span class="badge bg-primary-subtle text-primary fs-8 rounded-pill ms-1">{{ item.jpCount }} JP</span>
          </div>
        </div>
      </div>
    </template>
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

const days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

const periodNumbers = computed(() => {
  const periods = new Set<number>();
  props.timeSlots.forEach(slot => periods.add(slot.period_number));
  return Array.from(periods).sort((a, b) => a - b);
});

const getSlot = (day: string, periodNumber: number): TimeSlot | undefined => {
  return props.timeSlots.find(
    slot => slot.day.toLowerCase() === day.toLowerCase() && slot.period_number === periodNumber
  );
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
  const text = getContrastTextColor(bg);
  return {
    backgroundColor: bg,
    color: text,
  };
};

// Menghitung Legenda Dinamis khusus kelas ini
const legendItems = computed(() => {
  const map = new Map<string, { colorHex: string; subjectName: string; teacherName: string; jpCount: number }>();

  Object.values(props.schedules || {}).forEach((sched) => {
    if (sched && sched.status === 'published' && sched.class_subject_teacher) {
      const cst = sched.class_subject_teacher;
      const subjName = cst.class_subject?.subject?.subject_name || '-';
      const tchrName = cst.teacher?.name || '-';
      const color = cst.class_subject?.subject?.color_code || '#3b82f6';

      const key = `${subjName}_${tchrName}`;
      if (map.has(key)) {
        map.get(key)!.jpCount += 1;
      } else {
        map.set(key, {
          colorHex: color,
          subjectName: subjName,
          teacherName: tchrName,
          jpCount: 1,
        });
      }
    }
  });

  return Array.from(map.values());
});
</script>

<style scoped>
.slot-cell {
  height: 62px;
  width: 14%;
}
.bg-break {
  background-color: #f1f5f9 !important;
}
.full-block-tile {
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}
.legend-swatch {
  width: 10px;
  height: 10px;
}
.fs-8 { font-size: 0.72rem; }
.shadow-2xs { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03); }
</style>