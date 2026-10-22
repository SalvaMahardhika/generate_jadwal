<template>
  <SidebarAdmin>
    <div class="container-fluid px-0">
      
      <!-- Page Header -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4 pb-2 border-bottom">
        <div>
          <h5 class="fw-bold text-dark mb-1">Jam & Slot Waktu</h5>
          <p class="text-secondary small mb-0">Kelola master jam pelajaran (JP), jam istirahat, dan profil slot waktu per jenjang/kelas.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
          <!-- Tombol Generate Otomatis -->
          <button 
            type="button" 
            class="btn btn-outline-primary rounded-3 py-2 px-3 small fw-medium d-flex align-items-center gap-2"
            :disabled="!timeSlotStore.activeProfileId"
            @click="openGenerateModal()"
          >
            <i class="bi bi-magic"></i>
            <span>Generate Otomatis</span>
          </button>

          <!-- Tombol Tambah Slot Waktu Manual -->
          <button 
            type="button" 
            class="btn btn-primary rounded-3 py-2 px-3 small fw-medium d-flex align-items-center gap-2"
            :disabled="!timeSlotStore.activeProfileId"
            @click="openCreateModal()"
          >
            <i class="bi bi-plus-lg"></i>
            <span>Tambah Slot Waktu</span>
          </button>
        </div>
      </div>

      <!-- Alert Pesan Sukses -->
      <div v-if="timeSlotStore.successMessage" class="alert alert-success border-0 rounded-3 py-2 px-3 small d-flex align-items-center justify-content-between mb-3" role="alert">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill"></i>
          <span>{{ timeSlotStore.successMessage }}</span>
        </div>
        <button type="button" class="btn-close small" @click="timeSlotStore.successMessage = null"></button>
      </div>

      <!-- Alert Pesan Error Global -->
      <div v-if="timeSlotStore.errorMessage" class="alert alert-danger border-0 rounded-3 py-2 px-3 small d-flex align-items-center justify-content-between mb-3" role="alert">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <span>{{ timeSlotStore.errorMessage }}</span>
        </div>
        <button type="button" class="btn-close small" @click="timeSlotStore.errorMessage = null"></button>
      </div>

      <!-- Selector Tab Profil Slot Waktu -->
      <ProfileTabSelector
        @openCreateProfile="openCreateProfileModal"
        @openEditProfile="openEditProfileModal"
        @openAssignClasses="openAssignClassesModal"
        @openDeleteProfile="confirmDeleteProfile"
      />

      <!-- Filter Bar -->
      <TimeSlotFilter :daysList="daysList" />

      <!-- Loading State Global -->
      <div v-if="timeSlotStore.isLoading" class="text-center py-5 bg-white rounded-3 border">
        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
        <span class="text-muted small">Memuat susunan slot waktu...</span>
      </div>

      <!-- Grid Cards Per Hari -->
      <div v-else-if="timeSlotStore.activeProfileId" class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
        <div v-for="day in activeDays" :key="day" class="col">
          <TimeSlotDayCard 
            :day="day"
            :slots="groupedTimeSlots[day] || []"
            @addSlot="openCreateModal"
            @editSlot="openEditModal"
            @deleteSlot="openDeleteModal"
            @clearDay="openClearDayModal"
          />
        </div>
      </div>

      <!-- Empty State ketika belum ada profil -->
      <div v-else class="text-center py-5 bg-white rounded-3 border text-muted">
        <i class="bi bi-collection fs-2 d-block mb-2 text-secondary"></i>
        <p class="small mb-2">Belum ada profil slot waktu yang dipilih atau dibuat.</p>
        <button type="button" class="btn btn-sm btn-primary rounded-3" @click="openCreateProfileModal">
          Buat Profil Slot Pertama
        </button>
      </div>

    </div>

    <!-- Modal Form Profil Slot (Tambah / Edit) -->
    <ProfileFormModal
      :show="showProfileModal"
      :isEditing="isEditingProfile"
      :isSaving="timeSlotStore.isSaving"
      :form="profileForm"
      :errors="profileErrors"
      @close="closeProfileModal"
      @submit="submitProfileForm"
    />

    <!-- Modal Plotting Profil ke Kelas -->
    <AssignProfileModal
      :show="showAssignModal"
      :profile="timeSlotStore.activeProfile"
      :isSaving="timeSlotStore.isSaving"
      :isLoadingClasses="isLoadingClasses"
      :classList="classList"
      @close="closeAssignModal"
      @submit="submitAssignClasses"
    />

    <!-- Modal Form Slot Waktu (Tambah / Edit Manual) -->
    <TimeSlotFormModal 
      :show="showFormModal"
      :isEditing="isEditing"
      :isSaving="timeSlotStore.isSaving"
      :daysList="daysList"
      :form="form"
      :errors="formErrors"
      @close="closeFormModal"
      @submit="submitForm"
      @dayChanged="onDayChangeInModal"
    />

    <!-- Modal Delete Single Slot / Delete All Day Slots -->
    <TimeSlotDeleteModal 
      :show="showDeleteModal"
      :slot="selectedSlot"
      :targetDay="selectedDayToClear"
      :isClearDay="isClearDayAction"
      :isSaving="timeSlotStore.isSaving"
      :errorMessage="deleteErrorMessage"
      @close="closeDeleteModal"
      @confirm="confirmDelete"
    />

    <!-- Modal Generate Slot Waktu Otomatis -->
    <TimeSlotGenerateModal 
      :show="showGenerateModal"
      :isSaving="timeSlotStore.isSaving"
      :daysList="daysList"
      @close="closeGenerateModal"
      @submit="submitGenerate"
    />

  </SidebarAdmin>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';
import SidebarAdmin from '../../layouts/SidebarAdmin.vue';
import TimeSlotFilter from './components/timeslot/TimeSlotFilter.vue';
import TimeSlotDayCard from './components/timeslot/TimeSlotDayCard.vue';
import TimeSlotFormModal from './components/timeslot/TimeSlotFormModal.vue';
import TimeSlotDeleteModal from './components/timeslot/TimeSlotDeleteModal.vue';
import TimeSlotGenerateModal from './components/timeslot/TimeSlotGenerateModal.vue';
import ProfileTabSelector from './components/timeslot/profile/ProfileTabSelector.vue';
import ProfileFormModal from './components/timeslot/profile/ProfileFormModal.vue';
import AssignProfileModal from './components/timeslot/profile/AssignProfileModal.vue';
import { useTimeSlotStore } from '../../stores/timeSlot';
import api from '../../services/api';
import type { TimeSlot, TimeSlotPayload, DayOfWeek, GenerateTimeSlotPayload, TimeSlotProfile, TimeSlotProfilePayload } from '../../types/timeSlot';

const timeSlotStore = useTimeSlotStore();

// --- STATE MODAL PROFIL ---
const showProfileModal = ref(false);
const isEditingProfile = ref(false);
const showAssignModal = ref(false);
const isLoadingClasses = ref(false);
const classList = ref<any[]>([]);

const profileForm = reactive<TimeSlotProfilePayload>({
  academic_year_id: 1,
  name: '',
  description: '',
});
const profileErrors = reactive<Record<string, string[]>>({});

// --- STATE MODAL SLOT WAKTU ---
const showFormModal = ref(false);
const showDeleteModal = ref(false);
const showGenerateModal = ref(false);
const isEditing = ref(false);
const isClearDayAction = ref(false);
const selectedSlot = ref<TimeSlot | null>(null);
const selectedDayToClear = ref<DayOfWeek | null>(null);
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
  timeSlotStore.fetchProfiles();
});

// --- METODE PROFIL ---
const openCreateProfileModal = () => {
  isEditingProfile.value = false;
  profileForm.name = '';
  profileForm.description = '';
  Object.keys(profileErrors).forEach(k => delete profileErrors[k]);
  showProfileModal.value = true;
};

const openEditProfileModal = (profile: TimeSlotProfile) => {
  isEditingProfile.value = true;
  profileForm.academic_year_id = profile.academic_year_id;
  profileForm.name = profile.name;
  profileForm.description = profile.description || '';
  Object.keys(profileErrors).forEach(k => delete profileErrors[k]);
  showProfileModal.value = true;
};

const closeProfileModal = () => {
  if (!timeSlotStore.isSaving) showProfileModal.value = false;
};

const submitProfileForm = async () => {
  Object.keys(profileErrors).forEach(k => delete profileErrors[k]);
  let res;
  if (isEditingProfile.value && timeSlotStore.activeProfileId) {
    res = await timeSlotStore.updateProfile(timeSlotStore.activeProfileId, profileForm);
  } else {
    res = await timeSlotStore.createProfile(profileForm);
  }

  if (res.success) {
    showProfileModal.value = false;
  } else if (res.errors) {
    Object.assign(profileErrors, res.errors);
  }
};

const confirmDeleteProfile = async (profile: TimeSlotProfile) => {
  if (confirm(`Apakah Anda yakin ingin menghapus profil "${profile.name}"? Seluruh slot waktu dalam profil ini akan ikut terhapus.`)) {
    await timeSlotStore.deleteProfile(profile.id);
  }
};

const openAssignClassesModal = async (profile: TimeSlotProfile) => {
  showAssignModal.value = true;
  isLoadingClasses.value = true;
  try {
    const res = await api.get('/classes', { params: { academic_year_id: profile.academic_year_id } });
    classList.value = res.data?.data || res.data || [];
  } catch (err) {
    classList.value = [];
  } finally {
    isLoadingClasses.value = false;
  }
};

const closeAssignModal = () => {
  if (!timeSlotStore.isSaving) showAssignModal.value = false;
};

const submitAssignClasses = async (classIds: number[]) => {
  if (!timeSlotStore.activeProfileId) return;
  const res = await timeSlotStore.assignProfileToClasses(timeSlotStore.activeProfileId, classIds);
  if (res.success) {
    showAssignModal.value = false;
  }
};

// --- METODE DETAIL SLOT WAKTU ---
const formatTime = (timeStr: string) => {
  if (!timeStr) return '';
  return timeStr.substring(0, 5);
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
    const sorted = [...existingSlots].sort((a, b) => timeToMinutes(a.end_time) - timeToMinutes(b.end_time));
    const lastSlot = sorted[sorted.length - 1];
    if (lastSlot && lastSlot.end_time) {
      autoStart = formatTime(lastSlot.end_time);
      const startMins = timeToMinutes(autoStart);
      const endMins = startMins + 35;
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

const onDayChangeInModal = (newDay: DayOfWeek) => {
  if (isEditing.value) return;
  resetForm(newDay);
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

const openGenerateModal = () => {
  showGenerateModal.value = true;
};

const closeGenerateModal = () => {
  if (!timeSlotStore.isSaving) {
    showGenerateModal.value = false;
  }
};

const submitGenerate = async (payload: GenerateTimeSlotPayload) => {
  const result = await timeSlotStore.generateTimeSlots({
    ...payload,
    time_slot_profile_id: timeSlotStore.activeProfileId!,
  });
  if (result.success) {
    showGenerateModal.value = false;
  }
};

const submitForm = async () => {
  Object.keys(formErrors).forEach(key => delete formErrors[key]);

  const cleanStartTime = sanitizeTimeInput(form.start_time);
  const cleanEndTime = sanitizeTimeInput(form.end_time);

  // 1. Validasi Bentrok JP Ke-
  const isDuplicatePeriod = timeSlotStore.timeSlots.some((slot) => {
    if (isEditing.value && selectedSlot.value && slot.id === selectedSlot.value.id) {
      return false;
    }
    return slot.day === form.day && Number(slot.period_number) === Number(form.period_number);
  });

  if (isDuplicatePeriod) {
    formErrors.period_number = [`JP ${form.period_number} sudah terdaftar pada hari ${form.day}.`];
  }

  // 2. Validasi Waktu Selesai > Waktu Mulai
  const startMin = timeToMinutes(cleanStartTime);
  const endMin = timeToMinutes(cleanEndTime);

  if (endMin <= startMin) {
    formErrors.end_time = ['Waktu selesai harus setelah waktu mulai.'];
  }

  // 3. Validasi Overlap Rentang Waktu
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

  if (Object.keys(formErrors).length > 0) {
    return;
  }

  const payload: TimeSlotPayload = {
    time_slot_profile_id: timeSlotStore.activeProfileId!,
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
  selectedDayToClear.value = null;
  isClearDayAction.value = false;
  deleteErrorMessage.value = null;
  showDeleteModal.value = true;
};

const openClearDayModal = (day: DayOfWeek) => {
  selectedSlot.value = null;
  selectedDayToClear.value = day;
  isClearDayAction.value = true;
  deleteErrorMessage.value = null;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  if (!timeSlotStore.isSaving) {
    showDeleteModal.value = false;
  }
};

const confirmDelete = async () => {
  deleteErrorMessage.value = null;

  if (isClearDayAction.value && selectedDayToClear.value) {
    const result = await timeSlotStore.deleteTimeSlotsByDay(selectedDayToClear.value);

    if (result.success) {
      showDeleteModal.value = false;
    } else {
      deleteErrorMessage.value = result.message || `Gagal menghapus slot waktu di hari ${selectedDayToClear.value}.`;
    }
  } else if (selectedSlot.value) {
    const result = await timeSlotStore.deleteTimeSlot(selectedSlot.value.id);

    if (result.success) {
      showDeleteModal.value = false;
    } else {
      deleteErrorMessage.value = result.message || 'Gagal menghapus slot waktu.';
    }
  }
};
</script>