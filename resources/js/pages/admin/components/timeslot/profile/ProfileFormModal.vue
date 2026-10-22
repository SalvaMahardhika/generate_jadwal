<template>
  <Teleport to="body">
    <div v-if="show" class="modal-backdrop fade show"></div>
    <div v-if="show" class="modal fade show d-block" tabindex="-1" @click.self="emit('close')">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden bg-white">
          
          <div class="modal-header border-bottom px-4 py-3">
            <h6 class="modal-title fw-bold text-dark">
              {{ isEditing ? 'Edit Profil Slot Waktu' : 'Tambah Profil Slot Waktu Baru' }}
            </h6>
            <button type="button" class="btn-close small shadow-none" @click="emit('close')" :disabled="isSaving"></button>
          </div>

          <form @submit.prevent="handleSubmit">
            <div class="modal-body p-4">
              
              <!-- Nama Profil -->
              <div class="mb-3">
                <label for="profile_name" class="form-label fw-medium text-dark small">Nama Profil <span class="text-danger">*</span></label>
                <input 
                  type="text" 
                  id="profile_name" 
                  class="form-control form-control-sm rounded-3 shadow-none"
                  :class="{ 'is-invalid': errors.name }"
                  placeholder="Contoh: SD Kelas 1-2 (1x Istirahat) / SMK Reguler"
                  v-model="form.name"
                  required
                >
                <div v-if="errors.name" class="invalid-feedback small">{{ errors.name[0] }}</div>
              </div>

              <!-- Deskripsi -->
              <div class="mb-0">
                <label for="profile_desc" class="form-label fw-medium text-dark small">Deskripsi (Opsional)</label>
                <textarea 
                  id="profile_desc" 
                  rows="2"
                  class="form-control form-control-sm rounded-3 shadow-none"
                  placeholder="Keterangan singkat mengenai penggunaan profil slot waktu ini..."
                  v-model="form.description"
                ></textarea>
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
import type { TimeSlotProfilePayload } from '../../../../../types/timeSlot';

defineProps<{
  show: boolean;
  isEditing: boolean;
  isSaving: boolean;
  form: TimeSlotProfilePayload;
  errors: Record<string, string[]>;
}>();

const emit = defineEmits(['close', 'submit']);

const handleSubmit = () => {
  emit('submit');
};
</script>