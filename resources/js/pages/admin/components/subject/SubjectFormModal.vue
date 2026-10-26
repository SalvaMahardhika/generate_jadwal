<template>
  <Teleport to="body">
    <div v-if="show" class="modal-backdrop fade show"></div>
    <div v-if="show" class="modal fade show d-block" tabindex="-1" @click.self="closeModal">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden bg-white">
          
          <div class="modal-header border-bottom px-4 py-3">
            <h6 class="modal-title fw-bold text-dark">{{ isEditing ? 'Edit Mata Pelajaran' : 'Tambah Mapel Baru' }}</h6>
            <button type="button" class="btn-close small shadow-none" @click="closeModal" :disabled="isSaving"></button>
          </div>

          <form @submit.prevent="handleSubmit">
            <div class="modal-body p-4">
              
              <!-- Kode & Nama -->
              <div class="row g-3 mb-3">
                <div class="col-12 col-sm-4">
                  <label for="subject_code" class="form-label fw-medium text-dark small">Kode Mapel <span class="text-danger">*</span></label>
                  <input 
                    type="text" 
                    id="subject_code" 
                    class="form-control form-control-sm rounded-3 shadow-none text-uppercase"
                    :class="{ 'is-invalid': errors.subject_code }"
                    placeholder="MTK"
                    v-model="form.subject_code"
                    required
                  >
                  <div v-if="errors.subject_code" class="invalid-feedback small">{{ errors.subject_code[0] }}</div>
                </div>

                <div class="col-12 col-sm-8">
                  <label for="subject_name" class="form-label fw-medium text-dark small">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                  <input 
                    type="text" 
                    id="subject_name" 
                    class="form-control form-control-sm rounded-3 shadow-none"
                    :class="{ 'is-invalid': errors.subject_name }"
                    placeholder="Matematika"
                    v-model="form.subject_name"
                    required
                  >
                  <div v-if="errors.subject_name" class="invalid-feedback small">{{ errors.subject_name[0] }}</div>
                </div>
              </div>

              <!-- Kategori & KKM -->
              <div class="row g-3 mb-3">
                <div class="col-12 col-sm-6">
                  <label for="category" class="form-label fw-medium text-dark small">Kategori Mapel <span class="text-danger">*</span></label>
                  <select 
                    id="category" 
                    class="form-select form-select-sm rounded-3 shadow-none"
                    :class="{ 'is-invalid': errors.category }"
                    v-model="form.category"
                    required
                  >
                    <option value="" disabled>-- Pilih Kategori --</option>
                    <option value="wajib">Wajib</option>
                    <option value="peminatan">Peminatan</option>
                    <option value="mulok">Muatan Lokal</option>
                    <option value="kejuruan">Kejuruan</option>
                    <option value="kokurikuler">Kokurikuler</option>
                  </select>
                  <div v-if="errors.category" class="invalid-feedback small">{{ errors.category[0] }}</div>
                </div>

                <div class="col-12 col-sm-6">
                  <label for="passing_grade" class="form-label fw-medium text-dark small">KKM / Nilai Kelulusan</label>
                  <input 
                    type="number" 
                    id="passing_grade" 
                    min="0"
                    max="100"
                    class="form-control form-control-sm rounded-3 shadow-none"
                    :class="{ 'is-invalid': errors.passing_grade }"
                    placeholder="75"
                    v-model.number="form.passing_grade"
                  >
                  <div v-if="errors.passing_grade" class="invalid-feedback small">{{ errors.passing_grade[0] }}</div>
                </div>
              </div>

              <!-- Warna Label & Status -->
              <div class="row g-3 mb-3">
                <div class="col-12 col-sm-6">
                  <label for="color_code" class="form-label fw-medium text-dark small">Warna Identifikasi</label>
                  <div class="d-flex align-items-center gap-2">
                    <input 
                      type="color" 
                      id="color_code" 
                      class="form-control form-control-color form-control-sm border-0 p-0 rounded-circle"
                      v-model="form.color_code"
                      title="Pilih Warna"
                    >
                    <span class="small font-monospace text-muted">{{ form.color_code }}</span>
                  </div>
                </div>

                <div class="col-12 col-sm-6">
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

              <!-- Switch Mata Pelajaran Bersama / Paralel -->
              <div class="p-3 bg-light rounded-3 border">
                <div class="form-check form-switch mb-0">
                  <input 
                    class="form-check-input cursor-pointer" 
                    type="checkbox" 
                    role="switch" 
                    id="is_joint_subject" 
                    v-model="form.is_joint_subject"
                  >
                  <label class="form-check-label fw-semibold text-dark small cursor-pointer" for="is_joint_subject">
                    Mata Pelajaran Bersama / Paralel
                  </label>
                </div>
                <div class="form-text fs-8 text-secondary mt-1 mb-0">
                  Jika diaktifkan, guru yang sama dapat mengajar mata pelajaran ini di beberapa kelas sekaligus pada slot waktu yang sama (misal: Kokurikuler).
                </div>
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
import type { Subject, SubjectPayload } from '../../../../types/subject';

const props = defineProps<{
  show: boolean;
  isEditing: boolean;
  subject: Subject | null;
  isSaving: boolean;
  errors: Record<string, string[]>;
}>();

const emit = defineEmits(['close', 'submit']);

const form = reactive<SubjectPayload>({
  subject_code: '',
  subject_name: '',
  color_code: '#2563eb',
  category: '',
  passing_grade: 75,
  status: 'active',
  is_joint_subject: false,
});

watch(() => props.show, (newVal) => {
  if (newVal) {
    if (props.isEditing && props.subject) {
      form.subject_code = props.subject.subject_code;
      form.subject_name = props.subject.subject_name;
      form.color_code = props.subject.color_code || '#2563eb';
      form.category = props.subject.category || '';
      form.passing_grade = props.subject.passing_grade ?? 75;
      form.status = props.subject.status;
      form.is_joint_subject = !!props.subject.is_joint_subject;
    } else {
      form.subject_code = '';
      form.subject_name = '';
      form.color_code = '#2563eb';
      form.category = '';
      form.passing_grade = 75;
      form.status = 'active';
      form.is_joint_subject = false;
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
    subject_code: form.subject_code.trim().toUpperCase(),
    subject_name: form.subject_name.trim(),
    color_code: form.color_code,
    category: form.category ? form.category.trim() : '',
    passing_grade: form.passing_grade,
    status: form.status,
    is_joint_subject: form.is_joint_subject,
  });
};
</script>

<style scoped>
.fs-8 {
  font-size: 0.75rem;
}
.cursor-pointer {
  cursor: pointer;
}
</style>