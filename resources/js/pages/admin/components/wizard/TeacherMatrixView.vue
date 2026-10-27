<template>
  <div class="card border rounded-3 bg-white overflow-hidden shadow-sm">
    <!-- CARD HEADER -->
    <div class="card-header bg-light border-bottom p-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
      <div>
        <h6 class="fw-bold text-dark mb-0 fs-7">
          <i class="bi bi-person-badge text-primary me-2"></i> Timeline Agenda Mengajar: {{ teacherName || '-' }}
        </h6>
        <small class="text-muted fs-8">Ringkasan jadwal mengajar harian berdasarkan jam dinding asli.</small>
      </div>
      <span class="badge bg-primary px-3 py-1.5 rounded-pill fs-8 align-self-start align-self-sm-center">
        Total {{ totalAssignedJp }} JP / Minggu
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

            <!-- LIST ITEM AGENDA -->
            <div class="card-body p-2.5 d-flex flex-column gap-2">
              <div v-if="getDailyAgenda(day).length === 0" class="text-center py-4 text-muted fs-8 opacity-75">
                <i class="bi bi-calendar-x d-block fs-5 mb-1 opacity-50"></i>
                Tidak ada jadwal mengajar
              </div>

              <div 
                v-else 
                v-for="agenda in getDailyAgenda(day)" 
                :key="agenda.key"
                class="p-2.5 rounded-3 border transition-all"
                :class="agenda.classList.length > 1 ? 'bg-info-subtle border-info-subtle' : 'bg-success-subtle border-success-subtle'"
              >
                <!-- WAKTU PELAJARAN -->
                <div class="d-flex align-items-center justify-content-between gap-1 mb-1.5 border-bottom pb-1 border-white">
                  <span class="fw-bold font-monospace fs-8 text-dark">
                    <i class="bi bi-clock me-1 text-primary"></i>{{ agenda.timeRange }}
                  </span>
                  <span class="badge bg-white text-dark border fs-9 fw-semibold">
                    Jam ke-{{ agenda.periodNumber }}
                  </span>
                </div>

                <!-- NAMA MAPEL / KEGIATAN -->
                <div class="fw-bold text-dark fs-8 mb-1 text-truncate">
                  {{ agenda.subjectName }}
                </div>

                <!-- DAFTAR KELAS & BADGE BERSAMA -->
                <div class="d-flex align-items-center justify-content-between gap-1">
                  <span class="fs-8 fw-semibold text-truncate" :class="agenda.classList.length > 1 ? 'text-info-emphasis' : 'text-success'">
                    <i class="bi bi-building me-1"></i>{{ agenda.classText }}
                  </span>

                  <span 
                    v-if="agenda.classList.length > 1" 
                    class="badge bg-info text-white fs-9 py-0.5 px-1.5 fw-semibold flex-shrink-0"
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
  </div>
</template>

<script setup lang="ts">
import type { TimeSlot, ScheduleItem } from '../../../../types/wizard';

interface DailyAgendaItem {
  key: string;
  periodNumber: number;
  timeRange: string;
  startTime: string;
  endTime: string;
  subjectName: string;
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

// Ambil dan kelompokkan agenda per Hari berdasarkan Jam ke-N & Mapel
const getDailyAgenda = (day: string): DailyAgendaItem[] => {
  if (!props.schedules) return [];
  const scheduleList = Object.values(props.schedules);

  // Filter jadwal sesuai hari
  const matchingSchedules = scheduleList.filter(sched => {
    const slot = sched.time_slot || (props.timeSlots || []).find(s => s.id === sched.time_slot_id);
    if (!slot) return false;
    return slot.day.toLowerCase() === day.toLowerCase() && !slot.is_break;
  });

  if (matchingSchedules.length === 0) return [];

  // Grouping berdasarkan Mapel/Kegiatan + Jam ke-N (period_number)
  const groups: Record<string, DailyAgendaItem> = {};

  matchingSchedules.forEach(sched => {
    const slot = sched.time_slot || (props.timeSlots || []).find(s => s.id === sched.time_slot_id);
    if (!slot) return;

    const subjectName = sched.class_subject_teacher?.class_subject?.subject?.subject_name 
      || sched.custom_activity 
      || 'Kegiatan';
      
    const className = sched.class?.class_name || 'Kelas';
    const startTime = slot.start_time ? slot.start_time.substring(0, 5) : '00:00';
    const endTime = slot.end_time ? slot.end_time.substring(0, 5) : '00:00';

    // Kunci pengelompokan menggunakan subjectName dan period_number agar tidak terpecah oleh profil waktu yang berbeda
    const groupKey = `${subjectName}-p${slot.period_number}`;

    if (!groups[groupKey]) {
      groups[groupKey] = {
        key: groupKey,
        periodNumber: slot.period_number,
        timeRange: `${startTime} - ${endTime}`,
        startTime,
        endTime,
        subjectName,
        classList: [],
        classText: '',
      };
    } else {
      // Jika ada variasi profil waktu, sesuaikan batas waktu terluar
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

  // Urutkan agenda harian secara kronologis berdasarkan Jam ke-N & format daftar kelas
  return Object.values(groups)
    .map(g => {
      g.classList.sort((a, b) => a.localeCompare(b, undefined, { numeric: true, sensitivity: 'base' }));
      g.classText = g.classList.join(', ');
      return g;
    })
    .sort((a, b) => a.periodNumber - b.periodNumber);
};
</script>

<style scoped>
.fs-7 { font-size: 0.85rem; }
.fs-8 { font-size: 0.73rem; }
.fs-9 { font-size: 0.65rem; }

.bg-success-subtle { background-color: #f0fdf4 !important; }
.border-success-subtle { border-color: #bbf7d0 !important; }

.bg-info-subtle { background-color: #f0f9ff !important; }
.border-info-subtle { border-color: #bae6fd !important; }
.text-info-emphasis { color: #0369a1 !important; }

.transition-all { transition: all 0.2s ease-in-out; }
</style>