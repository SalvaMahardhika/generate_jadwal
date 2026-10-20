<template>
  <Teleport to="body">
    <div v-if="show" class="modal-backdrop fade show"></div>
    <div v-if="show" class="modal fade show d-block" tabindex="-1" @click.self="emit('close')">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden bg-white">
          
          <div class="modal-header border-bottom px-4 py-3 bg-light">
            <div>
              <h6 class="modal-title fw-bold text-dark mb-0">Generate Slot Waktu Otomatis</h6>
              <small class="text-secondary fs-8">Buat susunan Jam Pelajaran (JP) dan waktu istirahat secara instan.</small>
            </div>
            <button type="button" class="btn-close small shadow-none" @click="emit('close')" :disabled="isSaving"></button>
          </div>

          <form @submit.prevent="handleSubmit">
            <div class="modal-body p-4">
              
              <!-- Section 1: Konfigurasi Umum -->
              <div class="mb-4">
                <h6 class="fw-semibold text-primary fs-7 mb-3">
                  <i class="bi bi-sliders me-1"></i> 1. Pengaturan Dasar
                </h6>
                <div class="row g-3">
                  <div class="col-12 col-sm-6">
                    <label for="start_time" class="form-label fw-medium text-dark small">Jam Mulai Sekolah <span class="text-danger">*</span></label>
                    <input 
                      type="text" 
                      id="start_time" 
                      class="form-control form-control-sm rounded-3 shadow-none font-monospace"
                      placeholder="07:00"
                      maxlength="5"
                      v-model="form.start_time"
                      required
                    >
                    <small class="text-muted fs-8">Format 24 Jam (misal: 07:00)</small>
                  </div>

                  <div class="col-12 col-sm-6">
                    <label for="period_duration" class="form-label fw-medium text-dark small">Durasi 1 JP (Menit) <span class="text-danger">*</span></label>
                    <input 
                      type="number" 
                      id="period_duration" 
                      min="15"
                      max="120"
                      class="form-control form-control-sm rounded-3 shadow-none"
                      placeholder="35"
                      v-model.number="form.period_duration"
                      required
                    >
                    <small class="text-muted fs-8">Standard: 35–45 menit per JP</small>
                  </div>
                </div>
              </div>

              <hr class="text-muted opacity-25 my-3" />

              <!-- Section 2: Pilihan Hari & Jumlah JP -->
              <div class="mb-4">
                <h6 class="fw-semibold text-primary fs-7 mb-3">
                  <i class="bi bi-calendar-week me-1"></i> 2. Pilihan Hari & Target JP
                </h6>

                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-2">
                  <div v-for="day in daysList" :key="day" class="col">
                    <div class="p-2 border rounded-3 bg-light d-flex align-items-center justify-content-between">
                      <div class="form-check mb-0">
                        <input 
                          class="form-check-input shadow-none" 
                          type="checkbox" 
                          :id="`check-${day}`"
                          :value="day"
                          v-model="form.days"
                        >
                        <label class="form-check-label text-capitalize fw-medium small" :for="`check-${day}`">
                          {{ day }}
                        </label>
                      </div>
                      
                      <div class="d-flex align-items-center gap-1" style="width: 85px;">
                        <input 
                          type="number" 
                          min="1" 
                          max="15"
                          class="form-control form-control-sm text-center py-0 px-1 shadow-none rounded-2"
                          :disabled="!form.days.includes(day)"
                          v-model.number="form.periods_per_day[day]"
                        >
                        <span class="fs-8 text-muted">JP</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <hr class="text-muted opacity-25 my-3" />

              <!-- Section 3: Pengaturan Jam Istirahat -->
              <div class="mb-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <h6 class="fw-semibold text-primary fs-7 mb-0">
                    <i class="bi bi-cup-hot me-1"></i> 3. Sisipan Jam Istirahat / ISHOMA
                  </h6>
                  <button 
                    type="button" 
                    class="btn btn-sm btn-outline-primary rounded-3 py-1 px-2 fs-8 fw-medium"
                    @click="addBreakRow"
                  >
                    + Tambah Istirahat
                  </button>
                </div>

                <div v-if="form.breaks.length === 0" class="text-center py-3 bg-light rounded-3 border border-dashed text-muted">
                  <small class="fs-8">Belum ada istirahat ditambahkan. Seluruh JP akan disusun berurutan tanpa jeda.</small>
                </div>

                <div v-else class="d-flex flex-column gap-2">
                  <div 
                    v-for="(b, index) in form.breaks" 
                    :key="index"
                    class="p-2.5 bg-light rounded-3 border d-flex flex-wrap align-items-center gap-2"
                  >
                    <div class="d-flex align-items-center gap-1 flex-grow-1">
                      <span class="fs-8 text-muted flex-shrink-0">Setelah JP Ke-</span>
                      <input 
                        type="number" 
                        min="1" 
                        max="12"
                        class="form-control form-control-sm text-center shadow-none rounded-2"
                        style="width: 65px;"
                        v-model.number="b.after_period"
                        required
                      >
                    </div>

                    <div class="d-flex align-items-center gap-1 flex-grow-1">
                      <span class="fs-8 text-muted flex-shrink-0">Durasi</span>
                      <input 
                        type="number" 
                        min="5" 
                        max="120"
                        class="form-control form-control-sm text-center shadow-none rounded-2"
                        style="width: 70px;"
                        v-model.number="b.duration"
                        required
                      >
                      <span class="fs-8 text-muted">Mnt</span>
                    </div>

                    <div class="flex-grow-1" style="min-width: 140px;">
                      <input 
                        type="text" 
                        class="form-control form-control-sm shadow-none rounded-2"
                        placeholder="Label (Contoh: Istirahat I)"
                        v-model="b.label"
                      >
                    </div>

                    <button 
                      type="button" 
                      class="btn btn-light btn-sm text-danger border-0 rounded-2 p-1 ms-auto"
                      title="Hapus Baris Istirahat"
                      @click="removeBreakRow(index)"
                    >
                      <i class="bi bi-trash fs-7"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Opsi Overwrite -->
              <div class="form-check mt-3">
                <input 
                  class="form-check-input shadow-none" 
                  type="checkbox" 
                  id="overwrite" 
                  v-model="form.overwrite"
                >
                <label class="form-check-label small text-secondary" for="overwrite">
                  Timpa / hapus seluruh slot waktu lama pada hari-hari yang dipilih di atas.
                </label>
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
                :disabled="isSaving || form.days.length === 0"
              >
                <span v-if="!isSaving"><i class="bi bi-magic me-1"></i> Generate Sekarang</span>
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
import { reactive } from 'vue';
import type { DayOfWeek, GenerateTimeSlotPayload } from '../../../../types/timeSlot';

const props = defineProps<{
  show: boolean;
  isSaving: boolean;
  daysList: DayOfWeek[];
}>();

const emit = defineEmits(['close', 'submit']);

const form = reactive<GenerateTimeSlotPayload>({
  start_time: '07:00',
  period_duration: 35,
  days: ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'],
  periods_per_day: {
    senin: 10,
    selasa: 10,
    rabu: 10,
    kamis: 10,
    jumat: 6,
    sabtu: 7,
  },
  breaks: [
    { after_period: 4, duration: 15, label: 'Istirahat I' },
    { after_period: 7, duration: 45, label: 'ISHOMA' },
  ],
  overwrite: true,
});

const addBreakRow = () => {
  const lastBreak = form.breaks[form.breaks.length - 1];
  const nextAfter = lastBreak ? lastBreak.after_period + 3 : 4;
  form.breaks.push({
    after_period: nextAfter,
    duration: 15,
    label: 'Istirahat',
  });
};

const removeBreakRow = (index: number) => {
  form.breaks.splice(index, 1);
};

const handleSubmit = () => {
  emit('submit', { ...form });
};
</script>

<style scoped>
.fs-7 {
  font-size: 0.82rem;
}
.fs-8 {
  font-size: 0.72rem;
}
.border-dashed {
  border-style: dashed !important;
}
</style>