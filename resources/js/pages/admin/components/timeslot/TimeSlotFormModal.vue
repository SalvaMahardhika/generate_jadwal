<template>
  <Teleport to="body">
    <div v-if="show" class="modal-backdrop fade show"></div>
    <div v-if="show" class="modal fade show d-block" tabindex="-1" @click.self="emit('close')">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden bg-white">
          
          <div class="modal-header border-bottom px-4 py-3">
            <h6 class="modal-title fw-bold text-dark">{{ isEditing ? 'Edit Slot Waktu' : 'Tambah Slot Waktu Baru' }}</h6>
            <button type="button" class="btn-close small shadow-none" @click="emit('close')" :disabled="isSaving"></button>
          </div>

          <form @submit.prevent="handleSubmit">
            <div class="modal-body p-4">
              
              <!-- Hari & Urutan JP -->
              <div class="row g-3 mb-3">
                <div class="col-12 col-sm-6">
                  <label for="slot_day" class="form-label fw-medium text-dark small">Hari <span class="text-danger">*</span></label>
                  <select 
                    id="slot_day" 
                    class="form-select form-select-sm rounded-3 shadow-none text-capitalize"
                    :class="{ 'is-invalid': errors.day }"
                    v-model="form.day"
                    @change="emit('dayChanged', form.day)"
                    required
                  >
                    <option v-for="d in daysList" :key="d" :value="d">{{ d }}</option>
                  </select>
                  <div v-if="errors.day" class="invalid-feedback small">{{ errors.day[0] }}</div>
                </div>

                <div class="col-12 col-sm-6">
                  <label for="slot_period_number" class="form-label fw-medium text-dark small">Urutan Jam (JP Ke-) <span class="text-danger">*</span></label>
                  <input 
                    type="number" 
                    id="slot_period_number" 
                    min="1"
                    class="form-control form-control-sm rounded-3 shadow-none"
                    :class="{ 'is-invalid': errors.period_number }"
                    placeholder="1"
                    v-model.number="form.period_number"
                    required
                  >
                  <div v-if="errors.period_number" class="invalid-feedback small">{{ errors.period_number[0] }}</div>
                </div>
              </div>

              <!-- Waktu Mulai, Durasi (Menit), & Waktu Selesai -->
              <div class="row g-3 mb-3">
                <div class="col-12 col-sm-4">
                  <label for="slot_start_time" class="form-label fw-medium text-dark small">Waktu Mulai <span class="text-danger">*</span></label>
                  <input 
                    type="text" 
                    id="slot_start_time" 
                    class="form-control form-control-sm rounded-3 shadow-none font-monospace"
                    :class="{ 'is-invalid': errors.start_time }"
                    placeholder="07:30"
                    maxlength="5"
                    v-model="form.start_time"
                    @input="onStartTimeChange"
                    @blur="onStartTimeBlur"
                    required
                  >
                  <small class="text-muted fs-8">Format 24 jam</small>
                  <div v-if="errors.start_time" class="invalid-feedback small">{{ errors.start_time[0] }}</div>
                </div>

                <div class="col-12 col-sm-4">
                  <label for="slot_duration" class="form-label fw-medium text-dark small">Durasi (Menit) <span class="text-danger">*</span></label>
                  <input 
                    type="number" 
                    id="slot_duration" 
                    min="1"
                    max="300"
                    class="form-control form-control-sm rounded-3 shadow-none"
                    placeholder="35"
                    v-model.number="duration"
                    @input="onDurationChange"
                    required
                  >
                  <small class="text-muted fs-8">Dalam menit</small>
                </div>

                <div class="col-12 col-sm-4">
                  <label for="slot_end_time" class="form-label fw-medium text-dark small">Waktu Selesai <span class="text-danger">*</span></label>
                  <input 
                    type="text" 
                    id="slot_end_time" 
                    class="form-control form-control-sm rounded-3 shadow-none font-monospace"
                    :class="{ 'is-invalid': errors.end_time }"
                    placeholder="08:15"
                    maxlength="5"
                    v-model="form.end_time"
                    @input="onEndTimeChange"
                    @blur="onEndTimeBlur"
                    required
                  >
                  <small class="text-muted fs-8">Format 24 jam</small>
                  <div v-if="errors.end_time" class="invalid-feedback small">{{ errors.end_time[0] }}</div>
                </div>
              </div>

              <!-- Label / Keterangan -->
              <div class="mb-3">
                <label for="slot_label" class="form-label fw-medium text-dark small">Label / Keterangan (Opsional)</label>
                <input 
                  type="text" 
                  id="slot_label" 
                  class="form-control form-control-sm rounded-3 shadow-none"
                  :class="{ 'is-invalid': errors.label }"
                  placeholder="Contoh: JP 10 (13.15 - 13.50) atau Istirahat Kedua"
                  v-model="form.label"
                >
                <div v-if="errors.label" class="invalid-feedback small">{{ errors.label[0] }}</div>
              </div>

              <!-- Tipe Slot -->
              <div class="mb-2">
                <label class="form-label fw-medium text-dark small">Tipe Slot</label>
                <select 
                  class="form-select form-select-sm rounded-3 shadow-none"
                  :class="{ 'is-invalid': errors.is_break }"
                  v-model="form.is_break"
                >
                  <option :value="false">Jam Pelajaran Biasa</option>
                  <option :value="true">Jam Istirahat / ISHOMA</option>
                </select>
                <div v-if="errors.is_break" class="invalid-feedback small">{{ errors.is_break[0] }}</div>
              </div>

            </div>

            <div class="modal-footer border-top px-4 py-3 bg-light">
              <button 
                type="button" 
                class="btn btn-light rounded-3 py-2 px-3 small fw-medium border shadow-none"
                @click="emit('close')"
                :disabled="isSaving"
              >
                Batal
              </button>
              <button 
                type="submit" 
                class="btn btn-primary rounded-3 py-2 px-3 small fw-semibold shadow-none d-flex align-items-center gap-2"
                :disabled="isSaving"
              >
                <span v-if="!isSaving">Simpan</span>
                <span v-else class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import type { TimeSlotPayload, DayOfWeek } from '../../../../types/timeSlot';

const props = defineProps<{
  show: boolean;
  isEditing: boolean;
  isSaving: boolean;
  daysList: DayOfWeek[];
  form: TimeSlotPayload;
  errors: Record<string, string[]>;
}>();

const emit = defineEmits(['close', 'submit', 'dayChanged']);

const duration = ref<number>(35);
let isInternalSyncing = false;

const timeToMinutes = (timeStr: string): number => {
  if (!timeStr) return 0;
  const clean = timeStr.trim().replace('.', ':');
  const parts = clean.split(':');
  if (parts.length < 2) return 0;
  const h = parseInt(parts[0], 10) || 0;
  const m = parseInt(parts[1], 10) || 0;
  return h * 60 + m;
};

const minutesToTime = (totalMins: number): string => {
  if (totalMins < 0) totalMins = 0;
  const h = String(Math.floor(totalMins / 60) % 24).padStart(2, '0');
  const m = String(totalMins % 60).padStart(2, '0');
  return `${h}:${m}`;
};

const sanitizeTimeInput = (val: string) => {
  if (!val) return '';
  return val.trim().replace('.', ':');
};

const syncDurationFromTimes = () => {
  const startMins = timeToMinutes(props.form.start_time);
  const endMins = timeToMinutes(props.form.end_time);
  if (endMins > startMins) {
    duration.value = endMins - startMins;
  }
};

watch(
  () => [props.show, props.form.start_time, props.form.end_time],
  () => {
    if (isInternalSyncing) return;
    syncDurationFromTimes();
  },
  { immediate: true }
);

const onDurationChange = () => {
  if (!duration.value || duration.value <= 0) return;
  isInternalSyncing = true;
  const startMins = timeToMinutes(props.form.start_time);
  props.form.end_time = minutesToTime(startMins + duration.value);
  setTimeout(() => { isInternalSyncing = false; }, 50);
};

const onStartTimeChange = () => {
  isInternalSyncing = true;
  const cleanStart = sanitizeTimeInput(props.form.start_time);
  if (cleanStart.length === 5 && duration.value > 0) {
    const startMins = timeToMinutes(cleanStart);
    props.form.end_time = minutesToTime(startMins + duration.value);
  } else {
    syncDurationFromTimes();
  }
  setTimeout(() => { isInternalSyncing = false; }, 50);
};

const onStartTimeBlur = () => {
  props.form.start_time = sanitizeTimeInput(props.form.start_time);
  onStartTimeChange();
};

const onEndTimeChange = () => {
  isInternalSyncing = true;
  syncDurationFromTimes();
  setTimeout(() => { isInternalSyncing = false; }, 50);
};

const onEndTimeBlur = () => {
  props.form.end_time = sanitizeTimeInput(props.form.end_time);
  onEndTimeChange();
};

const handleSubmit = () => {
  emit('submit');
};
</script>

<style scoped>
.fs-8 {
  font-size: 0.72rem;
}
</style>