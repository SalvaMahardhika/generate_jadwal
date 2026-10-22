<template>
  <Teleport to="body">
    <div v-if="show" class="modal-backdrop fade show"></div>
    <div v-if="show" class="modal fade show d-block" tabindex="-1" @click.self="emit('close')">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden bg-white">
          
          <div class="modal-header border-bottom px-4 py-3 bg-light">
            <div>
              <h6 class="modal-title fw-bold text-dark mb-0">Plotting Profil ke Kelas</h6>
              <small class="text-secondary fs-8">
                Pilih kelas-kelas yang akan menggunakan profil slot waktu <strong>{{ profile?.name }}</strong>.
              </small>
            </div>
            <button type="button" class="btn-close small shadow-none" @click="emit('close')" :disabled="isSaving"></button>
          </div>

          <div class="modal-body p-4">
            <div v-if="isLoadingClasses" class="text-center py-4">
              <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
              <span class="text-muted small">Memuat daftar kelas...</span>
            </div>

            <div v-else-if="classList.length === 0" class="text-center py-4 text-muted">
              <small>Belum ada data kelas pada tahun ajaran ini.</small>
            </div>

            <template v-else>
              <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="small fw-semibold text-dark">Daftar Kelas Tersedia</span>
                <button 
                  v-if="selectableClasses.length > 0"
                  type="button" 
                  class="btn btn-link btn-sm p-0 text-decoration-none small" 
                  @click="toggleSelectAll"
                >
                  {{ isAllSelectableSelected ? 'Batalkan Semua' : 'Pilih Semua Kelas' }}
                </button>
              </div>

              <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-2 style-scroll" style="max-height: 300px; overflow-y: auto;">
                <div v-for="cls in classList" :key="cls.id" class="col">
                  <div 
                    class="p-2 border rounded-3 d-flex align-items-center justify-content-between gap-2 transition-all"
                    :class="{
                      'border-primary bg-primary-subtle cursor-pointer': !isClassLocked(cls) && selectedClassIds.includes(cls.id),
                      'bg-light border-light-subtle cursor-pointer': !isClassLocked(cls) && !selectedClassIds.includes(cls.id),
                      'bg-body-tertiary border-secondary-subtle opacity-75 cursor-not-allowed': isClassLocked(cls)
                    }"
                    @click="toggleClass(cls)"
                  >
                    <div class="d-flex align-items-center gap-2 text-truncate me-1">
                      <input 
                        class="form-check-input shadow-none my-0" 
                        :class="{ 'cursor-pointer': !isClassLocked(cls), 'cursor-not-allowed': isClassLocked(cls) }"
                        type="checkbox" 
                        :value="cls.id"
                        :checked="selectedClassIds.includes(cls.id)"
                        :disabled="isClassLocked(cls)"
                      >
                      <div class="text-truncate">
                        <span class="fw-semibold small d-block text-truncate" :class="isClassLocked(cls) ? 'text-secondary' : 'text-dark'">
                          {{ cls.class_name }}
                        </span>
                        <small class="text-muted fs-8 d-block text-truncate">
                          {{ cls.grade?.name || '' }} {{ cls.major?.name ? `- ${cls.major.name}` : '' }}
                        </small>
                      </div>
                    </div>

                    <!-- Badge status jika kelas dikunci karena dipakai profil lain -->
                    <div v-if="isClassLocked(cls)" class="flex-shrink-0">
                      <span class="badge bg-secondary-subtle text-secondary border fs-8 fw-normal" :title="`Sudah di-plotting ke profil: ${cls.time_slot_profile?.name || 'Profil lain'}`">
                        <i class="bi bi-lock-fill me-1"></i>
                        {{ cls.time_slot_profile?.name || 'Profil Lain' }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </template>
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
              type="button" 
              class="btn btn-primary rounded-3 py-2 px-3 small fw-semibold shadow-none d-flex align-items-center gap-2"
              :disabled="isSaving"
              @click="handleAssign"
            >
              <span v-if="!isSaving">Simpan Plotting ({{ selectedClassIds.length }} Kelas)</span>
              <span v-else class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            </button>
          </div>

        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import type { TimeSlotProfile } from '../../../../../types/timeSlot';

const props = defineProps<{
  show: boolean;
  profile: TimeSlotProfile | null;
  isSaving: boolean;
  isLoadingClasses: boolean;
  classList: Array<{
    id: number;
    class_name: string;
    time_slot_profile_id?: number | null;
    grade?: { id: number; name: string };
    major?: { id: number; name: string };
    time_slot_profile?: { id: number; name: string } | null;
  }>;
}>();

const emit = defineEmits(['close', 'submit']);

const selectedClassIds = ref<number[]>([]);

// Mengecek apakah kelas terkunci (digunakan oleh profil slot waktu lain)
const isClassLocked = (cls: (typeof props.classList)[0]) => {
  return (
    !!cls.time_slot_profile_id &&
    cls.time_slot_profile_id !== props.profile?.id
  );
};

// Daftar kelas yang dapat dipilih (tidak terkunci)
const selectableClasses = computed(() => {
  return props.classList.filter((cls) => !isClassLocked(cls));
});

// Mengecek apakah seluruh kelas yang dapat dipilih sudah dicentang
const isAllSelectableSelected = computed(() => {
  if (selectableClasses.value.length === 0) return false;
  return selectableClasses.value.every((cls) => selectedClassIds.value.includes(cls.id));
});

watch(
  () => [props.show, props.profile, props.classList],
  () => {
    if (props.show && props.profile) {
      // Otomatis tandai kelas yang sudah terikat ke profil ini
      selectedClassIds.value = props.classList
        .filter((c) => c.time_slot_profile_id === props.profile?.id)
        .map((c) => c.id);
    }
  },
  { immediate: true }
);

const toggleClass = (cls: (typeof props.classList)[0]) => {
  if (isClassLocked(cls)) return; // Abaikan interaksi jika kelas digunakan profil lain

  const idx = selectedClassIds.value.indexOf(cls.id);
  if (idx === -1) {
    selectedClassIds.value.push(cls.id);
  } else {
    selectedClassIds.value.splice(idx, 1);
  }
};

const toggleSelectAll = () => {
  if (isAllSelectableSelected.value) {
    // Batalkan centang seluruh kelas yang bisa dipilih
    const selectableIds = selectableClasses.value.map((c) => c.id);
    selectedClassIds.value = selectedClassIds.value.filter((id) => !selectableIds.includes(id));
  } else {
    // Centang seluruh kelas yang bisa dipilih
    const currentSet = new Set(selectedClassIds.value);
    selectableClasses.value.forEach((c) => currentSet.add(c.id));
    selectedClassIds.value = Array.from(currentSet);
  }
};

const handleAssign = () => {
  emit('submit', selectedClassIds.value);
};
</script>

<style scoped>
.fs-8 {
  font-size: 0.72rem;
}
.cursor-pointer {
  cursor: pointer;
}
.cursor-not-allowed {
  cursor: not-allowed !important;
}
.bg-primary-subtle {
  background-color: #eff6ff !important;
}
.transition-all {
  transition: all 0.15s ease-in-out;
}
</style>