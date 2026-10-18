<template>
  <SidebarAdmin>
    <div class="container-fluid px-0">
      
      <!-- Page Header -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4 pb-2 border-bottom">
        <div>
          <h5 class="fw-bold text-dark mb-1">Jam & Slot Waktu</h5>
          <p class="text-secondary small mb-0">Kelola master jam pelajaran (JP), jam istirahat, dan alokasi waktu per hari.</p>
        </div>
        <button 
          type="button" 
          class="btn btn-primary rounded-3 py-2 px-3 small fw-medium d-flex align-items-center gap-2"
          @click="openCreateModal()"
        >
          <i class="bi bi-plus-lg"></i>
          <span>Tambah Slot Waktu</span>
        </button>
      </div>

      <!-- Alert Pesan Sukses -->
      <div v-if="timeSlotStore.successMessage" class="alert alert-success border-0 rounded-3 py-2 px-3 small d-flex align-items-center justify-content-between mb-3" role="alert">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill"></i>
          <span>{{ timeSlotStore.successMessage }}</span>
        </div>
        <button type="button" class="btn-close small" @click="timeSlotStore.successMessage = null"></button>
      </div>

      <!-- Filter Bar -->
      <div class="card border rounded-3 p-3 bg-white mb-4">
        <div class="row g-2 align-items-center">
          <!-- Filter Hari -->
          <div class="col-12 col-md-4">
            <select 
              class="form-select form-select-sm bg-light shadow-none text-capitalize" 
              v-model="timeSlotStore.filters.day"
              @change="timeSlotStore.fetchTimeSlots()"
            >
              <option value="">Semua Hari</option>
              <option v-for="d in daysList" :key="d" :value="d">{{ d }}</option>
            </select>
          </div>

          <!-- Filter Tipe Slot (JP / Istirahat) -->
          <div class="col-12 col-md-4">
            <select 
              class="form-select form-select-sm bg-light shadow-none" 
              v-model="timeSlotStore.filters.is_break"
              @change="timeSlotStore.fetchTimeSlots()"
            >
              <option value="">Semua Tipe Slot</option>
              <option value="0">Jam Pelajaran</option>
              <option value="1">Jam Istirahat / ISHOMA</option>
            </select>
          </div>

          <!-- Reset Filter Button -->
          <div class="col-12 col-md-4 text-md-end" v-if="timeSlotStore.filters.day || timeSlotStore.filters.is_break !== ''">
            <button 
              type="button" 
              class="btn btn-link text-decoration-none text-muted small p-0"
              @click="timeSlotStore.resetFilters()"
            >
              Reset Filter
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State Global -->
      <div v-if="timeSlotStore.isLoading" class="text-center py-5 bg-white rounded-3 border">
        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
        <span class="text-muted small">Memuat susunan slot waktu...</span>
      </div>

      <!-- Grid Cards Per Hari -->
      <div v-else class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
        <div v-for="day in activeDays" :key="day" class="col">
          <div class="card border rounded-3 bg-white h-100 shadow-sm overflow-hidden">
            
            <!-- Header Card Hari -->
            <div class="card-header bg-light border-bottom py-3 px-3 d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-calendar-day text-primary"></i>
                <h6 class="fw-bold text-dark text-capitalize mb-0">{{ day }}</h6>
                <span class="badge bg-primary-subtle text-primary rounded-pill fs-8">
                  {{ groupedTimeSlots[day]?.length || 0 }} Slot
                </span>
              </div>
              <button 
                type="button" 
                class="btn btn-sm btn-light border-0 text-primary p-1 rounded-2"
                title="Tambah Slot untuk Hari Ini"
                @click="openCreateModal(day)"
              >
                <i class="bi bi-plus-circle-fill"></i>
              </button>
            </div>

            <!-- List Slot Waktu Per Hari -->
            <div class="card-body p-0">
              <!-- Empty Day State -->
              <div v-if="!groupedTimeSlots[day] || groupedTimeSlots[day].length === 0" class="text-center py-4 px-3 text-muted">
                <i class="bi bi-clock fs-4 d-block mb-1 text-secondary opacity-50"></i>
                <small class="d-block mb-2">Belum ada slot waktu</small>
                <button 
                  type="button" 
                  class="btn btn-outline-primary btn-sm rounded-3 py-1 px-2 fs-8"
                  @click="openCreateModal(day)"
                >
                  + Tambah Slot
                </button>
              </div>

              <!-- List Group Slots -->
              <ul v-else class="list-group list-group-flush">
                <li 
                  v-for="slot in groupedTimeSlots[day]" 
                  :key="slot.id" 
                  class="list-group-item px-3 py-2.5 d-flex align-items-center justify-content-between slot-item"
                  :class="{ 'bg-warning-subtle-light': slot.is_break }"
                >
                  <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                    <!-- Badge JP / Istirahat -->
                    <span 
                      class="badge rounded-2 fw-semibold fs-8 flex-shrink-0"
                      :class="slot.is_break ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle'"
                    >
                      {{ slot.is_break ? 'IST' : `JP ${slot.period_number}` }}
                    </span>

                    <!-- Detail Jam & Label -->
                    <div class="text-truncate">
                      <div class="fw-semibold text-dark font-monospace fs-7">
                        {{ formatTime(slot.start_time) }} - {{ formatTime(slot.end_time) }}
                      </div>
                      <small v-if="slot.label" class="text-muted text-truncate d-block fs-8">{{ slot.label }}</small>
                    </div>
                  </div>

                  <!-- Tombol Aksi -->
                  <div class="d-flex gap-1 flex-shrink-0">
                    <button 
                      type="button" 
                      class="btn btn-light btn-sm text-secondary border-0 rounded-2 p-1"
                      title="Edit"
                      @click="openEditModal(slot)"
                    >
                      <i class="bi bi-pencil-square fs-7"></i>
                    </button>
                    <button 
                      type="button" 
                      class="btn btn-light btn-sm text-danger border-0 rounded-2 p-1"
                      title="Hapus"
                      @click="openDeleteModal(slot)"
                    >
                      <i class="bi bi-trash fs-7"></i>
                    </button>
                  </div>
                </li>
              </ul>
            </div>

          </div>
        </div>
      </div>

    </div>

    <!-- Modal Form (Tambah / Edit) -->
    <Teleport to="body">
      <div v-if="showFormModal" class="modal-backdrop fade show"></div>
      <div v-if="showFormModal" class="modal fade show d-block" tabindex="-1" @click.self="closeFormModal">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            
            <div class="modal-header border-bottom px-4 py-3">
              <h6 class="modal-title fw-bold text-dark">{{ isEditing ? 'Edit Slot Waktu' : 'Tambah Slot Waktu Baru' }}</h6>
              <button type="button" class="btn-close small shadow-none" @click="closeFormModal" :disabled="timeSlotStore.isSaving"></button>
            </div>

            <form @submit.prevent="submitForm">
              <div class="modal-body p-4">
                
                <!-- Hari & Urutan JP -->
                <div class="row g-3 mb-3">
                  <div class="col-12 col-sm-6">
                    <label for="day" class="form-label fw-medium text-dark small">Hari <span class="text-danger">*</span></label>
                    <select 
                      id="day" 
                      class="form-select form-select-sm rounded-3 shadow-none text-capitalize"
                      :class="{ 'is-invalid': formErrors.day }"
                      v-model="form.day"
                      @change="onDayChangeInModal"
                      required
                    >
                      <option v-for="d in daysList" :key="d" :value="d">{{ d }}</option>
                    </select>
                    <div v-if="formErrors.day" class="invalid-feedback small">{{ formErrors.day[0] }}</div>
                  </div>

                  <div class="col-12 col-sm-6">
                    <label for="period_number" class="form-label fw-medium text-dark small">Urutan Jam (JP Ke-) <span class="text-danger">*</span></label>
                    <input 
                      type="number" 
                      id="period_number" 
                      min="1"
                      class="form-control form-control-sm rounded-3 shadow-none"
                      :class="{ 'is-invalid': formErrors.period_number }"
                      placeholder="1"
                      v-model.number="form.period_number"
                      required
                    >
                    <div v-if="formErrors.period_number" class="invalid-feedback small">{{ formErrors.period_number[0] }}</div>
                  </div>
                </div>

                <!-- Waktu Mulai & Waktu Selesai (Format 24 Jam) -->
                <div class="row g-3 mb-3">
                  <div class="col-12 col-sm-6">
                    <label for="start_time" class="form-label fw-medium text-dark small">Waktu Mulai <span class="text-danger">*</span></label>
                    <input 
                      type="text" 
                      id="start_time" 
                      class="form-control form-control-sm rounded-3 shadow-none font-monospace"
                      :class="{ 'is-invalid': formErrors.start_time }"
                      placeholder="07:30"
                      maxlength="5"
                      v-model="form.start_time"
                      @blur="form.start_time = sanitizeTimeInput(form.start_time)"
                      required
                    >
                    <small class="text-muted fs-8">Format 24 jam (misal: 07:30 atau 13:15)</small>
                    <div v-if="formErrors.start_time" class="invalid-feedback small">{{ formErrors.start_time[0] }}</div>
                  </div>

                  <div class="col-12 col-sm-6">
                    <label for="end_time" class="form-label fw-medium text-dark small">Waktu Selesai <span class="text-danger">*</span></label>
                    <input 
                      type="text" 
                      id="end_time" 
                      class="form-control form-control-sm rounded-3 shadow-none font-monospace"
                      :class="{ 'is-invalid': formErrors.end_time }"
                      placeholder="08:15"
                      maxlength="5"
                      v-model="form.end_time"
                      @blur="form.end_time = sanitizeTimeInput(form.end_time)"
                      required
                    >
                    <small class="text-muted fs-8">Format 24 jam (misal: 08:15 atau 14:00)</small>
                    <div v-if="formErrors.end_time" class="invalid-feedback small">{{ formErrors.end_time[0] }}</div>
                  </div>
                </div>

                <!-- Label / Keterangan -->
                <div class="mb-3">
                  <label for="label" class="form-label fw-medium text-dark small">Label / Keterangan (Opsional)</label>
                  <input 
                    type="text" 
                    id="label" 
                    class="form-control form-control-sm rounded-3 shadow-none"
                    :class="{ 'is-invalid': formErrors.label }"
                    placeholder="Contoh: JP 10 (13.15 - 13.50) atau Istirahat Kedua"
                    v-model="form.label"
                  >
                  <div v-if="formErrors.label" class="invalid-feedback small">{{ formErrors.label[0] }}</div>
                </div>

                <!-- Tipe Slot (Flag Istirahat) -->
                <div class="mb-2">
                  <label class="form-label fw-medium text-dark small">Tipe Slot</label>
                  <select 
                    class="form-select form-select-sm rounded-3 shadow-none"
                    :class="{ 'is-invalid': formErrors.is_break }"
                    v-model="form.is_break"
                  >
                    <option :value="false">Jam Pelajaran Biasa</option>
                    <option :value="true">Jam Istirahat / ISHOMA</option>
                  </select>
                  <div v-if="formErrors.is_break" class="invalid-feedback small">{{ formErrors.is_break[0] }}</div>
                </div>

              </div>

              <div class="modal-footer border-top px-4 py-3 bg-light">
                <button 
                  type="button" 
                  class="btn btn-light rounded-3 py-2 px-3 small fw-medium border shadow-none"
                  @click="closeFormModal"
                  :disabled="timeSlotStore.isSaving"
                >
                  Batal
                </button>
                <button 
                  type="submit" 
                  class="btn btn-primary rounded-3 py-2 px-3 small fw-semibold shadow-none d-flex align-items-center gap-2"
                  :disabled="timeSlotStore.isSaving"
                >
                  <span v-if="!timeSlotStore.isSaving">Simpan</span>
                  <span v-else class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                </button>
              </div>
            </form>

          </div>
        </div>
      </div>
    </Teleport>

    <!-- Modal Delete -->
    <Teleport to="body">
      <div v-if="showDeleteModal" class="modal-backdrop fade show"></div>
      <div v-if="showDeleteModal" class="modal fade show d-block" tabindex="-1" @click.self="closeDeleteModal">
        <div class="modal-dialog modal-dialog-centered modal-sm">
          <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="modal-body text-center p-4">
              
              <div class="delete-icon mx-auto mb-3 text-danger bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-exclamation-triangle fs-4"></i>
              </div>
              
              <h6 class="fw-bold text-dark mb-1">Hapus Slot Waktu?</h6>
              <p class="text-secondary small mb-3">
                Apakah Anda yakin ingin menghapus slot waktu <strong class="text-capitalize">{{ selectedSlot?.day }} - JP {{ selectedSlot?.period_number }}</strong>?
              </p>

              <div v-if="deleteErrorMessage" class="alert alert-danger border-0 rounded-3 py-2 px-3 small text-start mb-3" role="alert">
                {{ deleteErrorMessage }}
              </div>

              <div class="d-flex gap-2">
                <button 
                  type="button" 
                  class="btn btn-light w-50 rounded-3 py-2 small fw-medium border shadow-none"
                  @click="closeDeleteModal"
                  :disabled="timeSlotStore.isSaving"
                >
                  Batal
                </button>
                <button 
                  type="button" 
                  class="btn btn-danger w-50 rounded-3 py-2 small fw-semibold shadow-none d-flex align-items-center justify-content-center gap-2"
                  @click="confirmDelete"
                  :disabled="timeSlotStore.isSaving"
                >
                  <span v-if="!timeSlotStore.isSaving">Hapus</span>
                  <span v-else class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                </button>
              </div>

            </div>
          </div>
        </div>
      </div>
    </Teleport>

  </SidebarAdmin>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';
import SidebarAdmin from '../../layouts/SidebarAdmin.vue';
import { useTimeSlotStore } from '../../stores/timeSlot';
import type { TimeSlot, TimeSlotPayload, DayOfWeek } from '../../types/timeSlot';

const timeSlotStore = useTimeSlotStore();

const showFormModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const selectedSlot = ref<TimeSlot | null>(null);
const deleteErrorMessage = ref<string | null>(null);

const daysList: DayOfWeek[] = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

const activeDays = computed(() => {
  if (timeSlotStore.filters.day) {
    return daysList.filter(d => d === timeSlotStore.filters.day);
  }
  return daysList;
});

const groupedTimeSlots = computed(() => {
  const map: Record<DayOfWeek, TimeSlot[]> = {
    senin: [],
    selasa: [],
    rabu: [],
    kamis: [],
    jumat: [],
    sabtu: [],
  };

  timeSlotStore.timeSlots.forEach((slot) => {
    if (map[slot.day]) {
      map[slot.day].push(slot);
    }
  });

  // Urutkan slot berdasarkan period_number secara kronologis
  Object.keys(map).forEach((dayKey) => {
    map[dayKey as DayOfWeek].sort((a, b) => a.period_number - b.period_number);
  });

  return map;
});

const form = reactive<TimeSlotPayload>({
  day: 'senin',
  period_number: 1,
  label: '',
  start_time: '07:30',
  end_time: '08:15',
  is_break: false,
});

const formErrors = reactive<Record<string, string[]>>({});

onMounted(() => {
  timeSlotStore.fetchTimeSlots();
});

const formatTime = (timeStr: string) => {
  if (!timeStr) return '';
  return timeStr.substring(0, 5); // Mengambil format HH:mm
};

const sanitizeTimeInput = (val: string) => {
  if (!val) return '';
  return val.trim().replace('.', ':');
};

const timeToMinutes = (timeStr: string): number => {
  if (!timeStr) return 0;
  const clean = sanitizeTimeInput(timeStr);
  const parts = clean.split(':');
  if (parts.length < 2) return 0;
  const hours = parseInt(parts[0], 10) || 0;
  const minutes = parseInt(parts[1], 10) || 0;
  return hours * 60 + minutes;
};

const resetForm = (targetDay?: DayOfWeek) => {
  const selectedDay = targetDay || 'senin';
  const existingSlots = groupedTimeSlots.value[selectedDay] || [];
  
  const nextPeriod = existingSlots.length > 0 
    ? Math.max(...existingSlots.map(s => Number(s.period_number))) + 1 
    : 1;

  let autoStart = '07:30';
  let autoEnd = '08:15';

  if (existingSlots.length > 0) {
    // Cari slot dengan waktu selesai paling akhir
    const sorted = [...existingSlots].sort((a, b) => timeToMinutes(a.end_time) - timeToMinutes(b.end_time));
    const lastSlot = sorted[sorted.length - 1];
    if (lastSlot && lastSlot.end_time) {
      autoStart = formatTime(lastSlot.end_time);
      const startMins = timeToMinutes(autoStart);
      const endMins = startMins + 35; // Durasi default 35 menit
      const h = String(Math.floor(endMins / 60) % 24).padStart(2, '0');
      const m = String(endMins % 60).padStart(2, '0');
      autoEnd = `${h}:${m}`;
    }
  }

  form.day = selectedDay;
  form.period_number = nextPeriod;
  form.label = '';
  form.start_time = autoStart;
  form.end_time = autoEnd;
  form.is_break = false;

  Object.keys(formErrors).forEach(key => delete formErrors[key]);
};

const onDayChangeInModal = () => {
  if (isEditing.value) return;
  resetForm(form.day);
};

const openCreateModal = (targetDay?: DayOfWeek) => {
  resetForm(targetDay);
  isEditing.value = false;
  selectedSlot.value = null;
  showFormModal.value = true;
};

const openEditModal = (slot: TimeSlot) => {
  resetForm(slot.day);
  isEditing.value = true;
  selectedSlot.value = slot;

  form.day = slot.day;
  form.period_number = slot.period_number;
  form.label = slot.label || '';
  form.start_time = formatTime(slot.start_time);
  form.end_time = formatTime(slot.end_time);
  form.is_break = Boolean(slot.is_break);

  showFormModal.value = true;
};

const closeFormModal = () => {
  if (!timeSlotStore.isSaving) {
    showFormModal.value = false;
  }
};

const submitForm = async () => {
  Object.keys(formErrors).forEach(key => delete formErrors[key]);

  const cleanStartTime = sanitizeTimeInput(form.start_time);
  const cleanEndTime = sanitizeTimeInput(form.end_time);

  // 1. Validasi Bentrok JP Ke- (Duplicate Period Number)
  const isDuplicatePeriod = timeSlotStore.timeSlots.some((slot) => {
    if (isEditing.value && selectedSlot.value && slot.id === selectedSlot.value.id) {
      return false;
    }
    return slot.day === form.day && Number(slot.period_number) === Number(form.period_number);
  });

  if (isDuplicatePeriod) {
    formErrors.period_number = [`JP ${form.period_number} sudah terdaftar pada hari ${form.day}.`];
  }

  // 2. Validasi Waktu Selesai Harus Lebih Besar dari Waktu Mulai
  const startMin = timeToMinutes(cleanStartTime);
  const endMin = timeToMinutes(cleanEndTime);

  if (endMin <= startMin) {
    formErrors.end_time = ['Waktu selesai harus setelah waktu mulai.'];
  }

  // 3. Validasi Bentrok Rentang Waktu (Overlap Time Slot)
  const overlappingSlot = timeSlotStore.timeSlots.find((slot) => {
    if (isEditing.value && selectedSlot.value && slot.id === selectedSlot.value.id) {
      return false;
    }
    if (slot.day !== form.day) {
      return false;
    }
    const sStart = timeToMinutes(slot.start_time);
    const sEnd = timeToMinutes(slot.end_time);
    return startMin < sEnd && endMin > sStart;
  });

  if (overlappingSlot) {
    const overlapLabel = overlappingSlot.is_break ? 'Jam Istirahat' : `JP ${overlappingSlot.period_number}`;
    formErrors.start_time = [
      `Waktu (${cleanStartTime} - ${cleanEndTime}) bentrok dengan ${overlapLabel} (${formatTime(overlappingSlot.start_time)} - ${formatTime(overlappingSlot.end_time)}).`
    ];
  }

  // Jika terdapat error validasi lokal, batalkan submit
  if (Object.keys(formErrors).length > 0) {
    return;
  }

  const payload: TimeSlotPayload = {
    day: form.day,
    period_number: form.period_number,
    label: form.label ? form.label.trim() : null,
    start_time: cleanStartTime,
    end_time: cleanEndTime,
    is_break: form.is_break,
  };

  let result;
  if (isEditing.value && selectedSlot.value) {
    result = await timeSlotStore.updateTimeSlot(selectedSlot.value.id, payload);
  } else {
    result = await timeSlotStore.createTimeSlot(payload);
  }

  if (result.success) {
    showFormModal.value = false;
  } else if (result.errors) {
    Object.assign(formErrors, result.errors);
  }
};

const openDeleteModal = (slot: TimeSlot) => {
  selectedSlot.value = slot;
  deleteErrorMessage.value = null;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  if (!timeSlotStore.isSaving) {
    showDeleteModal.value = false;
  }
};

const confirmDelete = async () => {
  if (!selectedSlot.value) return;

  deleteErrorMessage.value = null;
  const result = await timeSlotStore.deleteTimeSlot(selectedSlot.value.id);

  if (result.success) {
    showDeleteModal.value = false;
  } else {
    deleteErrorMessage.value = result.message || 'Gagal menghapus slot waktu.';
  }
};
</script>

<style scoped>
.delete-icon {
  width: 48px;
  height: 48px;
}
.bg-danger-subtle {
  background-color: #fef2f2 !important;
  color: #dc2626 !important;
}
.bg-primary-subtle {
  background-color: #eff6ff !important;
  color: #2563eb !important;
}
.bg-warning-subtle {
  background-color: #fffbeb !important;
  color: #d97706 !important;
}
.bg-warning-subtle-light {
  background-color: #fffdf5 !important;
}
.slot-item:hover {
  background-color: #f8fafc;
}
.fs-7 {
  font-size: 0.82rem;
}
.fs-8 {
  font-size: 0.72rem;
}
</style>