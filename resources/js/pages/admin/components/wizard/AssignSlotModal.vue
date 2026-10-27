<template>
  <Teleport to="body">
    <div v-if="show" class="modal-backdrop fade show"></div>
    <div v-if="show" class="modal fade show d-block" tabindex="-1" @click.self="handleClose">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden bg-white">
          
          <!-- MODAL HEADER -->
          <div class="modal-header border-bottom px-4 py-3">
            <div>
              <h6 class="modal-title fw-bold text-dark mb-0">Atur Slot Jadwal</h6>
              <small class="text-muted fs-8" v-if="timeSlot">
                {{ capitalize(timeSlot.day) }} - Jam ke-{{ timeSlot.period_number }} 
                ({{ formatTime(timeSlot.start_time) }} - {{ formatTime(timeSlot.end_time) }})
              </small>
            </div>
            <button 
              type="button" 
              class="btn-close small shadow-none" 
              @click="handleClose" 
              :disabled="isProcessing"
            ></button>
          </div>

          <!-- MODAL BODY -->
          <div class="modal-body p-4">
            
            <!-- Navigasi Tipe Isian (Mapel vs Rutin) -->
            <ul class="nav nav-pills nav-fill bg-light p-1 rounded-3 mb-3">
              <li class="nav-item" v-if="!isEditingRoutine">
                <button 
                  type="button" 
                  class="nav-link py-1.5 px-3 fs-8 fw-semibold rounded-2 transition-all"
                  :class="{ 'active bg-white text-primary shadow-sm': slotType === 'subject' }"
                  @click="switchSlotType('subject')"
                >
                  <i class="bi bi-journal-bookmark me-1"></i> Mata Pelajaran
                </button>
              </li>
              <li class="nav-item" v-if="!isEditingSubject">
                <button 
                  type="button" 
                  class="nav-link py-1.5 px-3 fs-8 fw-semibold rounded-2 transition-all"
                  :class="{ 'active bg-white text-primary shadow-sm': slotType === 'routine' }"
                  @click="switchSlotType('routine')"
                >
                  <i class="bi bi-flag me-1"></i> Kegiatan Rutin / Non-Mapel
                </button>
              </li>
            </ul>

            <!-- OPSI A: MATA PELAJARAN -->
            <div v-if="slotType === 'subject'">
              <div class="mb-3">
                <label class="form-label fw-medium text-dark small">Pilih Mapel & Guru Pengampu <span class="text-danger">*</span></label>
                <select 
                  class="form-select form-select-sm rounded-3 shadow-none border-secondary-subtle"
                  v-model="form.class_subject_teacher_id"
                >
                  <option :value="null">-- Pilih Mata Pelajaran --</option>
                  <option 
                    v-for="cst in availableSubjectsWithQuota" 
                    :key="cst.id" 
                    :value="cst.id"
                    :disabled="cst.isFull && cst.id !== currentSchedule?.class_subject_teacher_id"
                  >
                    {{ cst.subjectName }} - {{ cst.teacherName }} 
                    ({{ cst.assignedJp }}/{{ cst.maxJp }} JP) 
                    <template v-if="cst.isFull && cst.id !== currentSchedule?.class_subject_teacher_id">[PENUH]</template>
                  </option>
                </select>
              </div>
            </div>

            <!-- OPSI B: KEGIATAN RUTIN -->
            <div v-else>
              <div class="mb-3">
                <label class="form-label fw-medium text-dark small">Nama Kegiatan Rutin <span class="text-danger">*</span></label>
                <input 
                  type="text" 
                  class="form-control form-control-sm rounded-3 shadow-none border-secondary-subtle"
                  placeholder="Contoh: UPACARA, Senam Pagi, ISHOMA, Dhuha"
                  v-model="form.custom_activity"
                >
              </div>
            </div>

            <!-- TOGGLE KUNCI SLOT -->
            <div class="form-check form-switch p-2.5 bg-light rounded-3 border mb-2">
              <input 
                class="form-check-input shadow-none ms-0 me-2 cursor-pointer" 
                type="checkbox" 
                id="lock_switch"
                v-model="form.is_locked"
              >
              <label class="form-check-label small fw-medium text-dark cursor-pointer" for="lock_switch">
                <i class="bi bi-lock-fill text-warning me-1"></i>
                Gembok / Kunci Slot Ini
              </label>
              <small class="d-block text-muted fs-8 ms-4">
                Slot yang dikunci tidak akan diubah atau digeser oleh fitur Auto-Generate.
              </small>
            </div>

            <!-- TOGGLE TERAPKAN UNTUK SEMUA KELAS (HANYA MUNCUL PADA KEGIATAN RUTIN) -->
            <div v-if="slotType === 'routine'" class="form-check form-switch p-2.5 bg-light rounded-3 border mb-2">
              <input 
                class="form-check-input shadow-none ms-0 me-2 cursor-pointer" 
                type="checkbox" 
                id="apply_all_switch"
                v-model="form.apply_to_all_classes"
              >
              <label class="form-check-label small fw-medium text-dark cursor-pointer" for="apply_all_switch">
                <i class="bi bi-grid-fill text-primary me-1"></i>
                Terapkan untuk Semua Kelas
              </label>
              <small class="d-block text-muted fs-8 ms-4">
                Slot kegiatan ini akan otomatis disetting dan diterapkan ke seluruh kelas aktif.
              </small>
            </div>

          </div>

          <!-- MODAL FOOTER -->
          <div class="modal-footer border-top px-4 py-3 bg-light d-flex justify-content-between align-items-center">
            
            <!-- Pilihan Kosongkan Slot -->
            <div v-if="currentSchedule" class="position-relative">
              <button 
                type="button" 
                class="btn btn-outline-danger rounded-3 py-1.5 px-3 small fw-medium shadow-none d-flex align-items-center gap-1"
                @click="slotType === 'routine' ? (showClearMenu = !showClearMenu) : handleClear(false)"
                :disabled="isProcessing"
              >
                <i class="bi bi-trash me-1"></i> Kosongkan Slot
                <i v-if="slotType === 'routine'" class="bi bi-chevron-up fs-9 ms-1"></i>
              </button>

              <!-- Dropdown Kosongkan Slot (Hanya untuk Kegiatan Rutin) -->
              <div 
                v-if="showClearMenu && slotType === 'routine'" 
                class="position-absolute bottom-100 start-0 mb-1 bg-white border rounded-3 shadow-sm p-1" 
                style="min-width: 180px; z-index: 1050;"
              >
                <button 
                  type="button" 
                  class="btn btn-light btn-sm w-100 text-start fs-8 py-1.5 px-2 rounded-2 mb-1 border-0"
                  @click="handleClear(false)"
                >
                  <i class="bi bi-door-open me-1.5 text-muted"></i> Hanya Kelas Ini
                </button>
                <button 
                  type="button" 
                  class="btn btn-light btn-sm w-100 text-start fs-8 py-1.5 px-2 rounded-2 border-0 text-danger"
                  @click="handleClear(true)"
                >
                  <i class="bi bi-grid-fill me-1.5"></i> Semua Kelas
                </button>
              </div>
            </div>

            <!-- Tombol Aksi Batal & Simpan -->
            <div class="ms-auto d-flex gap-2">
              <button 
                type="button" 
                class="btn btn-light rounded-3 py-1.5 px-3 small fw-medium border shadow-none"
                @click="handleClose"
                :disabled="isProcessing"
              >
                Batal
              </button>
              <button 
                type="button" 
                class="btn btn-primary rounded-3 py-1.5 px-3 small fw-semibold shadow-none d-flex align-items-center gap-1.5"
                @click="handleSave"
                :disabled="isProcessing"
              >
                <span v-if="!isProcessing">Simpan</span>
                <span v-else class="spinner-border spinner-border-sm" role="status"></span>
              </button>
            </div>

          </div>

        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, reactive, watch, computed } from 'vue';
import { useWizardStore } from '../../../../stores/wizard';
import type { TimeSlot, ScheduleItem, ClassSubjectTeacherDetail } from '../../../../types/wizard';

const wizardStore = useWizardStore();

const props = defineProps<{
  show: boolean;
  timeSlot: TimeSlot | null;
  currentSchedule: ScheduleItem | null;
  availableSubjects: ClassSubjectTeacherDetail[];
  isProcessing: boolean;
}>();

const emit = defineEmits(['close', 'save', 'clear']);

const slotType = ref<'subject' | 'routine'>('subject');
const showClearMenu = ref(false);

const form = reactive({
  class_subject_teacher_id: null as number | null,
  custom_activity: '',
  is_locked: false,
  apply_to_all_classes: false,
});

// Deteksi apakah slot yang diedit adalah Kegiatan Rutin
const isEditingRoutine = computed(() => {
  return !!(
    props.currentSchedule &&
    props.currentSchedule.custom_activity &&
    props.currentSchedule.custom_activity.trim() !== ''
  );
});

// Deteksi apakah slot yang diedit adalah Mata Pelajaran
const isEditingSubject = computed(() => {
  return !!(
    props.currentSchedule &&
    props.currentSchedule.class_subject_teacher_id !== null &&
    props.currentSchedule.class_subject_teacher_id !== undefined &&
    !isEditingRoutine.value
  );
});

const availableSubjectsWithQuota = computed(() => {
  const currentSchedules = wizardStore.classMatrix?.schedules || {};
  const allScheduleValues = Object.values(currentSchedules);

  return props.availableSubjects.map((cst) => {
    const maxJp = (cst.jp !== undefined && cst.jp !== null && Number(cst.jp) > 0)
      ? Number(cst.jp)
      : (cst.class_subject?.total_jp || 0);

    const totalAssignedJp = allScheduleValues.filter(
      (sched) => sched.class_subject_teacher_id === cst.id
    ).length;

    const otherSlotsCount = allScheduleValues.filter(
      (sched) => sched.class_subject_teacher_id === cst.id && sched.time_slot_id !== props.timeSlot?.id
    ).length;

    const isFull = otherSlotsCount >= maxJp;

    return {
      ...cst,
      subjectName: cst.class_subject?.subject?.subject_name ?? '-',
      teacherName: cst.teacher?.name ?? '-',
      assignedJp: totalAssignedJp,
      maxJp,
      isFull,
    };
  });
});

const switchSlotType = (type: 'subject' | 'routine') => {
  slotType.value = type;
  showClearMenu.value = false;
  if (type === 'routine') {
    form.is_locked = true;
    form.apply_to_all_classes = true;
  } else {
    form.apply_to_all_classes = false;
  }
};

watch(() => props.show, (newVal) => {
  if (newVal) {
    showClearMenu.value = false;

    if (isEditingRoutine.value) {
      slotType.value = 'routine';
      form.custom_activity = props.currentSchedule?.custom_activity || '';
      form.class_subject_teacher_id = null;
      form.is_locked = props.currentSchedule?.is_locked ?? true;
      form.apply_to_all_classes = true;
    } else {
      slotType.value = 'subject';
      form.class_subject_teacher_id = props.currentSchedule?.class_subject_teacher_id || null;
      form.custom_activity = '';
      form.is_locked = props.currentSchedule?.is_locked || false;
      form.apply_to_all_classes = false;
    }
  }
});

const handleClose = () => {
  showClearMenu.value = false;
  emit('close');
};

const handleSave = () => {
  showClearMenu.value = false;
  emit('save', {
    class_subject_teacher_id: slotType.value === 'subject' ? form.class_subject_teacher_id : null,
    custom_activity: slotType.value === 'routine' ? (form.custom_activity.trim() || null) : null,
    is_locked: form.is_locked,
    apply_to_all_classes: slotType.value === 'routine' ? form.apply_to_all_classes : false,
  });
};

const handleClear = (clearAllClasses: boolean = false) => {
  showClearMenu.value = false;
  const forceClearAll = slotType.value === 'subject' ? false : clearAllClasses;
  emit('clear', { clear_all_classes: forceClearAll });
};

const capitalize = (str: string) => (str ? str.charAt(0).toUpperCase() + str.slice(1) : '');

const formatTime = (timeStr?: string | null) => {
  if (!timeStr) return '--:--';
  return timeStr.length >= 5 ? timeStr.substring(0, 5) : timeStr;
};
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}
.fs-8 {
  font-size: 0.72rem;
}
.fs-9 {
  font-size: 0.65rem;
}
.transition-all {
  transition: all 0.2s ease-in-out;
}
</style>