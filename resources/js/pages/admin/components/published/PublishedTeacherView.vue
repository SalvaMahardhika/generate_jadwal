<template>
  <div class="card border rounded-3 bg-white overflow-hidden shadow-sm">
    <!-- CARD HEADER RINGKASAN GURU -->
    <div class="card-header bg-light border-bottom p-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
      <div>
        <h6 class="fw-bold text-dark mb-0 fs-7">
          <i class="bi bi-person-badge text-primary me-2"></i> Agenda Mengajar Resmi: {{ teacherName || '-' }}
        </h6>
        <small class="text-muted fs-8">Ringkasan jadwal mengajar harian resmi terpublikasi berdasarkan jam dinding asli.</small>
      </div>
      <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fs-8 align-self-start align-self-sm-center fw-semibold">
        <i class="bi bi-check-circle me-1"></i>Total Beban: {{ totalAssignedJp }} JP / Minggu
      </span>
    </div>

    <!-- CARD BODY / TIMELINE CONTENT -->
    <div class="card-body p-3 p-md-4">
      <!-- LOADING STATE -->
      <div v-if="isLoading" class="text-center py-5">
        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
        <span class="text-muted small">Memuat agenda mengajar guru...</span>
      </div>

      <!-- TIMELINE GRID PER HARI -->
      <div v-else class="row g-3">
        <div v-for="day in days" :key="day" class="col-12 col-md-6 col-xl-4">
          <div class="card border rounded-3 h-100 bg-white shadow-none">
            
            <!-- HEADER HARI -->
            <div class="card-header bg-light border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
              <span class="fw-bold text-dark small text-uppercase">
                <i class="bi bi-calendar-day me-1.5 text-primary"></i> {{ day }}
              </span>
              <span class="badge bg-secondary-subtle text-secondary fs-9 fw-semibold">
                {{ getDailyAgenda(day).length }} Agenda
              </span>
            </div>

            <!-- LIST ITEM AGENDA HARI INI -->
            <div class="card-body p-2.5 d-flex flex-column gap-2">
              <div v-if="getDailyAgenda(day).length === 0" class="text-center py-4 text-muted fs-8 opacity-75">
                <i class="bi bi-calendar-x d-block fs-5 mb-1 opacity-50"></i>
                Tidak ada jadwal mengajar
              </div>

              <div 
                v-else 
                v-for="agenda in getDailyAgenda(day)" 
                :key="agenda.key"
                class="p-2.5 rounded-3 border transition-all full-block-tile"
                :style="getTileStyle(agenda.colorCode)"
              >
                <!-- WAKTU PELAJARAN -->
                <div class="d-flex align-items-center justify-content-between gap-1 mb-1.5 border-bottom pb-1 border-white border-opacity-25">
                  <span class="fw-bold font-monospace fs-8">
                    <i class="bi bi-clock me-1"></i>{{ agenda.timeRange }}
                  </span>
                  <span class="badge bg-white text-dark border fs-9 fw-bold">
                    Jam ke-{{ agenda.periodNumber }}
                  </span>
                </div>

                <!-- NAMA MAPEL / KEGIATAN -->
                <div class="fw-bold fs-8 mb-1 text-truncate">
                  {{ agenda.subjectName }}
                </div>

                <!-- DAFTAR KELAS & BADGE BERSAMA -->
                <div class="d-flex align-items-center justify-content-between gap-1">
                  <span class="fs-8 fw-semibold text-truncate opacity-90">
                    <i class="bi bi-building me-1"></i>{{ agenda.classText }}
                  </span>

                  <span 
                    v-if="agenda.classList.length > 1" 
                    class="badge bg-warning text-dark fs-9 py-0.5 px-1.5 fw-bold flex-shrink-0"
                  >
                    Bersama ({{ agenda.classList.length }} Kelas)
                  </span>
                </div>

              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- LEGENDA WARNA MAPEL DAN KELAS -->
    <div v-if="legendItems.length > 0" class="card-footer bg-light border-top p-3">
      <h6 class="fs-8 fw-bold text-uppercase text-secondary mb-2">
        <i class="bi bi-palette me-1.5"></i>Mata Pelajaran & Kelas Yang Diampu:
      </h6>
      <div class="d-flex flex-wrap gap-2">
        <div 
          v-for="(item, idx) in legendItems" 
          :key="idx"
          class="d-flex align-items-center gap-1.5 bg-white border rounded-2 px-2.5 py-1 shadow-2xs"
        >
          <span class="legend-swatch rounded-circle flex-shrink-0" :style="{ backgroundColor: item.colorHex }"></span>
          <span class="fs-8 fw-semibold text-dark">{{ item.subjectName }}</span>
          <span class="fs-8 text-muted">• {{ item.className }}</span>
          <span class="badge bg-primary-subtle text-primary fs-8 rounded-pill ms-1">{{ item.jpCount }} JP</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { TimeSlot, ScheduleItem } from '../../../../types/wizard';

interface DailyAgendaItem {
  key: string;
  periodNumber: number;
  timeRange: string;
  startTime: string;
  endTime: string;
  subjectName: string;
  colorCode: string;
  classList: string[];
  classText: string;
}

const props = defineProps<{
  teacherName?: string;
  totalAssignedJp: number;
  timeSlots: TimeSlot[];
  schedules: Record<number, ScheduleItem>;
  isLoading: boolean;
}>();

const days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

// Menghitung kontras warna font (Putih/Hitam) berdasarkan warna background HEX
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

// Pengelompokan agenda harian guru (Murni menyaring status === 'published')
const getDailyAgenda = (day: string): DailyAgendaItem[] => {
  if (!props.schedules) return [];
  const scheduleList = Object.values(props.schedules);

  // Filter jadwal sesuai hari dan status published
  const matchingSchedules = scheduleList.filter(sched => {
    if (!sched || sched.status !== 'published') return false;
    const slot = sched.time_slot || (props.timeSlots || []).find(s => s.id === sched.time_slot_id);
    if (!slot) return false;
    return slot.day.toLowerCase() === day.toLowerCase() && !slot.is_break;
  });

  if (matchingSchedules.length === 0) return [];

  const groups: Record<string, DailyAgendaItem> = {};

  matchingSchedules.forEach(sched => {
    const slot = sched.time_slot || (props.timeSlots || []).find(s => s.id === sched.time_slot_id);
    if (!slot) return;

    const cst = sched.class_subject_teacher;
    const subjectName = cst?.class_subject?.subject?.subject_name 
      || sched.custom_activity 
      || 'Kegiatan';
      
    const colorCode = cst?.class_subject?.subject?.color_code || '#3b82f6';
    const className = sched.class?.class_name || 'Kelas';
    const startTime = slot.start_time ? slot.start_time.substring(0, 5) : '00:00';
    const endTime = slot.end_time ? slot.end_time.substring(0, 5) : '00:00';

    const groupKey = `${subjectName}-p${slot.period_number}`;

    if (!groups[groupKey]) {
      groups[groupKey] = {
        key: groupKey,
        periodNumber: slot.period_number,
        timeRange: `${startTime} - ${endTime}`,
        startTime,
        endTime,
        subjectName,
        colorCode,
        classList: [],
        classText: '',
      };
    } else {
      if (startTime < groups[groupKey].startTime) {
        groups[groupKey].startTime = startTime;
      }
      if (endTime > groups[groupKey].endTime) {
        groups[groupKey].endTime = endTime;
      }
      groups[groupKey].timeRange = `${groups[groupKey].startTime} - ${groups[groupKey].endTime}`;
    }

    if (!groups[groupKey].classList.includes(className)) {
      groups[groupKey].classList.push(className);
    }
  });

  return Object.values(groups)
    .map(g => {
      g.classList.sort((a, b) => a.localeCompare(b, undefined, { numeric: true, sensitivity: 'base' }));
      g.classText = g.classList.join(', ');
      return g;
    })
    .sort((a, b) => a.periodNumber - b.periodNumber);
};

// Legenda Mata Pelajaran dan Kelas yang Diampu
const legendItems = computed(() => {
  const map = new Map<string, { colorHex: string; subjectName: string; className: string; jpCount: number }>();

  Object.values(props.schedules || {}).forEach((sched) => {
    if (sched && sched.status === 'published' && sched.class_subject_teacher) {
      const cst = sched.class_subject_teacher;
      const subjName = cst.class_subject?.subject?.subject_name || '-';
      const className = sched.class?.class_name || '-';
      const color = cst.class_subject?.subject?.color_code || '#3b82f6';

      const key = `${subjName}_${className}`;
      if (map.has(key)) {
        map.get(key)!.jpCount += 1;
      } else {
        map.set(key, { colorHex: color, subjectName: subjName, className, jpCount: 1 });
      }
    }
  });

  return Array.from(map.values());
});
</script>

<style scoped>
.fs-7 { font-size: 0.85rem; }
.fs-8 { font-size: 0.73rem; }
.fs-9 { font-size: 0.65rem; }

.bg-success-subtle { background-color: #f0fdf4 !important; }
.border-success-subtle { border-color: #bbf7d0 !important; }

.full-block-tile {
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}
.legend-swatch { 
  width: 10px; 
  height: 10px; 
}
.shadow-2xs { 
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03); 
}
.transition-all { 
  transition: all 0.2s ease-in-out; 
}
</style>