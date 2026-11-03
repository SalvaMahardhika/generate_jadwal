<template>
  <Teleport to="body">
    <div v-if="show" class="modal-backdrop fade show"></div>
    <div v-if="show" class="modal fade show d-block" tabindex="-1" @click.self="closeModal">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden bg-white">
          
          <div class="modal-header border-bottom px-4 py-3 d-flex align-items-center justify-content-between">
            <h6 class="modal-title fw-bold text-dark">{{ isEditing ? 'Edit Data Guru' : 'Tambah Guru Baru' }}</h6>

            <div class="d-flex align-items-center gap-2">
              <button 
                v-if="!isEditing"
                type="button" 
                class="btn btn-sm btn-outline-success border-0 bg-success-subtle text-success fs-8 fw-medium py-1 px-2.5 rounded-2 d-flex align-items-center gap-1 shadow-none"
                @click="emit('openImport')"
                :disabled="isSaving"
                title="Impor massal data dari berkas Excel"
              >
                <i class="bi bi-file-earmark-excel"></i> Impor Excel
              </button>

              <button type="button" class="btn-close small shadow-none" @click="closeModal" :disabled="isSaving"></button>
            </div>
          </div>

          <form @submit.prevent="handleSubmit">
            <div class="modal-body p-4">
              
              <!-- NIP -->
              <div class="mb-3">
                <label for="nip" class="form-label fw-medium text-dark small">NIP (Opsional)</label>
                <input 
                  type="text" 
                  id="nip" 
                  class="form-control form-control-sm rounded-3 shadow-none"
                  :class="{ 'is-invalid': errors.nip }"
                  placeholder="Contoh: 198501012010011001"
                  v-model="form.nip"
                >
                <div v-if="errors.nip" class="invalid-feedback small">{{ errors.nip[0] }}</div>
              </div>

              <!-- Nama Lengkap -->
              <div class="mb-3">
                <label for="name" class="form-label fw-medium text-dark small">Nama Lengkap Guru <span class="text-danger">*</span></label>
                <input 
                  type="text" 
                  id="name" 
                  class="form-control form-control-sm rounded-3 shadow-none"
                  :class="{ 'is-invalid': errors.name }"
                  placeholder="Contoh: Drs. Budi Santoso, M.Pd"
                  v-model="form.name"
                  required
                >
                <div v-if="errors.name" class="invalid-feedback small">{{ errors.name[0] }}</div>
              </div>

              <!-- Gender & Max JP per Minggu -->
              <div class="row g-3 mb-3">
                <div class="col-12 col-sm-6">
                  <label class="form-label fw-medium text-dark small">Jenis Kelamin <span class="text-danger">*</span></label>
                  <select 
                    class="form-select form-select-sm rounded-3 shadow-none"
                    :class="{ 'is-invalid': errors.gender }"
                    v-model="form.gender"
                    required
                  >
                    <option value="L">Laki-Laki</option>
                    <option value="P">Perempuan</option>
                  </select>
                  <div v-if="errors.gender" class="invalid-feedback small">{{ errors.gender[0] }}</div>
                </div>

                <div class="col-12 col-sm-6">
                  <label for="max_jp" class="form-label fw-medium text-dark small">Max JP/Minggu <span class="text-danger">*</span></label>
                  <input 
                    type="number" 
                    id="max_jp" 
                    min="1"
                    class="form-control form-control-sm rounded-3 shadow-none"
                    :class="{ 'is-invalid': errors.max_jp_per_week }"
                    v-model.number="form.max_jp_per_week"
                    required
                  >
                  <div v-if="errors.max_jp_per_week" class="invalid-feedback small">{{ errors.max_jp_per_week[0] }}</div>
                </div>
              </div>

              <!-- Status -->
              <div class="mb-2">
                <label class="form-label fw-medium text-dark small">Status Keaktifan</label>
                <select 
                  class="form-select form-select-sm rounded-3 shadow-none"
                  :class="{ 'is-invalid': errors.status }"
                  v-model="form.status"
                >
                  <option value="active">Aktif</option>
                  <option value="inactive">Tidak Aktif</option>
                </select>
                <div v-if="errors.status" class="invalid-feedback small">{{ errors.status[0] }}</div>
              </div>

            </div>

            <div class="modal-footer border-top px-4 py-3 bg-light">
              <button 
                type="button" 
                class="btn btn-light rounded-3 py-2 px-3 small fw-medium border shadow-none"
                @click="closeModal"
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
import { reactive, watch } from 'vue';
import type { Teacher, TeacherPayload } from '../../../../types/teacher';

const props = defineProps<{
  show: boolean;
  isEditing: boolean;
  teacher: Teacher | null;
  isSaving: boolean;
  errors: Record<string, string[]>;
}>();

const emit = defineEmits(['close', 'submit', 'openImport']);

const form = reactive<TeacherPayload>({
  nip: '',
  name: '',
  gender: 'L',
  max_jp_per_week: 24,
  status: 'active',
});

watch(() => props.show, (newVal) => {
  if (newVal) {
    if (props.isEditing && props.teacher) {
      form.nip = props.teacher.nip || '';
      form.name = props.teacher.name;
      form.gender = props.teacher.gender;
      form.max_jp_per_week = props.teacher.max_jp_per_week ?? 24;
      form.status = props.teacher.status;
    } else {
      form.nip = '';
      form.name = '';
      form.gender = 'L';
      form.max_jp_per_week = 24;
      form.status = 'active';
    }
  }
});

const closeModal = () => {
  if (!props.isSaving) {
    emit('close');
  }
};

const handleSubmit = () => {
  emit('submit', {
    nip: form.nip ? form.nip.trim() : null,
    name: form.name.trim(),
    gender: form.gender,
    max_jp_per_week: form.max_jp_per_week,
    status: form.status,
  });
};
</script>

<style scoped>
.fs-8 { font-size: 0.75rem; }
.bg-success-subtle { background-color: #f0fdf4 !important; color: #16a34a !important; }
</style>