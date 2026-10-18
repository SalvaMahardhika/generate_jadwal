<template>
  <SidebarAdmin>
    <div class="container-fluid px-0">
      
      <!-- Page Header -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4 pb-2 border-bottom">
        <div>
          <h5 class="fw-bold text-dark mb-1">Mata Pelajaran</h5>
          <p class="text-secondary small mb-0">Kelola master kode mapel, nama, KKM, warna label, dan kategori mata pelajaran.</p>
        </div>
        <button 
          type="button" 
          class="btn btn-primary rounded-3 py-2 px-3 small fw-medium d-flex align-items-center gap-2"
          @click="openCreateModal"
        >
          <i class="bi bi-plus-lg"></i>
          <span>Tambah Mapel</span>
        </button>
      </div>

      <!-- Alert Pesan Sukses -->
      <div v-if="subjectStore.successMessage" class="alert alert-success border-0 rounded-3 py-2 px-3 small d-flex align-items-center justify-content-between mb-3" role="alert">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill"></i>
          <span>{{ subjectStore.successMessage }}</span>
        </div>
        <button type="button" class="btn-close small" @click="subjectStore.successMessage = null"></button>
      </div>

      <!-- Filter Bar -->
      <div class="card border rounded-3 p-3 bg-white mb-3">
        <div class="row g-2 align-items-center">
          <!-- Input Search -->
          <div class="col-12 col-md-4">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
              <input 
                type="text" 
                class="form-control bg-light border-start-0 shadow-none" 
                placeholder="Cari kode atau nama mapel..."
                v-model="subjectStore.filters.search"
                @input="handleSearch"
              >
            </div>
          </div>

          <!-- Filter Kategori -->
          <div class="col-12 col-md-3">
            <select 
              class="form-select form-select-sm bg-light shadow-none" 
              v-model="subjectStore.filters.category"
              @change="subjectStore.fetchSubjects()"
            >
              <option value="">Semua Kategori</option>
              <option value="wajib">Wajib</option>
              <option value="peminatan">Peminatan</option>
              <option value="mulok">Muatan Lokal</option>
              <option value="kejuruan">Kejuruan</option>
              <option value="kokurikuler">Kokurikuler</option>
            </select>
          </div>

          <!-- Filter Status -->
          <div class="col-12 col-md-3">
            <select 
              class="form-select form-select-sm bg-light shadow-none" 
              v-model="subjectStore.filters.status"
              @change="subjectStore.fetchSubjects()"
            >
              <option value="">Semua Status</option>
              <option value="active">Aktif</option>
              <option value="inactive">Tidak Aktif</option>
            </select>
          </div>

          <!-- Reset Filter Button -->
          <div class="col-12 col-md-2 text-md-end" v-if="subjectStore.filters.search || subjectStore.filters.status || subjectStore.filters.category">
            <button 
              type="button" 
              class="btn btn-link text-decoration-none text-muted small p-0"
              @click="subjectStore.resetFilters()"
            >
              Reset Filter
            </button>
          </div>
        </div>
      </div>

      <!-- Table Card -->
      <div class="card border rounded-3 bg-white overflow-hidden">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="bg-light border-bottom">
              <tr>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small" style="width: 50px;">#</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Kode</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Nama Mata Pelajaran</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Kategori</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">KKM</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Status</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small text-end" style="width: 120px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <!-- Loading -->
              <tr v-if="subjectStore.isLoading">
                <td colspan="7" class="text-center py-5 text-muted small">
                  <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                  Memuat data mata pelajaran...
                </td>
              </tr>

              <!-- Empty -->
              <tr v-else-if="subjectStore.subjects.length === 0">
                <td colspan="7" class="text-center py-5 text-muted small">
                  <i class="bi bi-inbox fs-3 d-block mb-2 text-secondary"></i>
                  Data mata pelajaran tidak ditemukan.
                </td>
              </tr>

              <!-- Rows -->
              <tr v-else v-for="(subject, index) in subjectStore.subjects" :key="subject.id">
                <td class="px-3 small text-secondary">{{ index + 1 }}</td>
                <td class="px-3 small font-monospace fw-bold text-dark">
                  <span class="d-inline-block rounded-circle me-1" :style="{ backgroundColor: subject.color_code || '#6c757d', width: '8px', height: '8px' }"></span>
                  {{ subject.subject_code }}
                </td>
                <td class="px-3 small fw-medium text-dark">{{ subject.subject_name }}</td>
                <td class="px-3 small text-secondary text-capitalize">{{ subject.category || '-' }}</td>
                <td class="px-3 small fw-semibold text-dark">{{ subject.passing_grade || '-' }}</td>
                <td class="px-3 small">
                  <span 
                    class="badge rounded-pill px-2 py-1 fw-normal fs-8"
                    :class="subject.status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'"
                  >
                    {{ subject.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                  </span>
                </td>
                <td class="px-3 text-end">
                  <div class="d-flex justify-content-end gap-1">
                    <button 
                      type="button" 
                      class="btn btn-light btn-sm text-secondary border-0 rounded-2 px-2 py-1"
                      title="Edit"
                      @click="openEditModal(subject)"
                    >
                      <i class="bi bi-pencil-square"></i>
                    </button>
                    <button 
                      type="button" 
                      class="btn btn-light btn-sm text-danger border-0 rounded-2 px-2 py-1"
                      title="Hapus"
                      @click="openDeleteModal(subject)"
                    >
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
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
              <h6 class="modal-title fw-bold text-dark">{{ isEditing ? 'Edit Mata Pelajaran' : 'Tambah Mapel Baru' }}</h6>
              <button type="button" class="btn-close small shadow-none" @click="closeFormModal" :disabled="subjectStore.isSaving"></button>
            </div>

            <form @submit.prevent="submitForm">
              <div class="modal-body p-4">
                
                <!-- Kode & Nama -->
                <div class="row g-3 mb-3">
                  <div class="col-12 col-sm-4">
                    <label for="subject_code" class="form-label fw-medium text-dark small">Kode Mapel <span class="text-danger">*</span></label>
                    <input 
                      type="text" 
                      id="subject_code" 
                      class="form-control form-control-sm rounded-3 shadow-none text-uppercase"
                      :class="{ 'is-invalid': formErrors.subject_code }"
                      placeholder="MTK"
                      v-model="form.subject_code"
                      required
                    >
                    <div v-if="formErrors.subject_code" class="invalid-feedback small">{{ formErrors.subject_code[0] }}</div>
                  </div>

                  <div class="col-12 col-sm-8">
                    <label for="subject_name" class="form-label fw-medium text-dark small">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                    <input 
                      type="text" 
                      id="subject_name" 
                      class="form-control form-control-sm rounded-3 shadow-none"
                      :class="{ 'is-invalid': formErrors.subject_name }"
                      placeholder="Matematika"
                      v-model="form.subject_name"
                      required
                    >
                    <div v-if="formErrors.subject_name" class="invalid-feedback small">{{ formErrors.subject_name[0] }}</div>
                  </div>
                </div>

                <!-- Kategori & KKM -->
                <div class="row g-3 mb-3">
                  <div class="col-12 col-sm-6">
                    <label for="category" class="form-label fw-medium text-dark small">Kategori Mapel <span class="text-danger">*</span></label>
                    <select 
                      id="category" 
                      class="form-select form-select-sm rounded-3 shadow-none"
                      :class="{ 'is-invalid': formErrors.category }"
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
                    <div v-if="formErrors.category" class="invalid-feedback small">{{ formErrors.category[0] }}</div>
                  </div>

                  <div class="col-12 col-sm-6">
                    <label for="passing_grade" class="form-label fw-medium text-dark small">KKM / Nilai Kelulusan</label>
                    <input 
                      type="number" 
                      id="passing_grade" 
                      min="0"
                      max="100"
                      class="form-control form-control-sm rounded-3 shadow-none"
                      :class="{ 'is-invalid': formErrors.passing_grade }"
                      placeholder="75"
                      v-model.number="form.passing_grade"
                    >
                    <div v-if="formErrors.passing_grade" class="invalid-feedback small">{{ formErrors.passing_grade[0] }}</div>
                  </div>
                </div>

                <!-- Warna Label & Status -->
                <div class="row g-3 mb-2">
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
                      :class="{ 'is-invalid': formErrors.status }"
                      v-model="form.status"
                    >
                      <option value="active">Aktif</option>
                      <option value="inactive">Tidak Aktif</option>
                    </select>
                    <div v-if="formErrors.status" class="invalid-feedback small">{{ formErrors.status[0] }}</div>
                  </div>
                </div>

              </div>

              <div class="modal-footer border-top px-4 py-3 bg-light">
                <button 
                  type="button" 
                  class="btn btn-light rounded-3 py-2 px-3 small fw-medium border shadow-none"
                  @click="closeFormModal"
                  :disabled="subjectStore.isSaving"
                >
                  Batal
                </button>
                <button 
                  type="submit" 
                  class="btn btn-primary rounded-3 py-2 px-3 small fw-semibold shadow-none d-flex align-items-center gap-2"
                  :disabled="subjectStore.isSaving"
                >
                  <span v-if="!subjectStore.isSaving">Simpan</span>
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
              
              <h6 class="fw-bold text-dark mb-1">Hapus Mata Pelajaran?</h6>
              <p class="text-secondary small mb-3">
                Apakah Anda yakin ingin menghapus <strong>{{ selectedSubject?.subject_name }}</strong>?
              </p>

              <div v-if="deleteErrorMessage" class="alert alert-danger border-0 rounded-3 py-2 px-3 small text-start mb-3" role="alert">
                {{ deleteErrorMessage }}
              </div>

              <div class="d-flex gap-2">
                <button 
                  type="button" 
                  class="btn btn-light w-50 rounded-3 py-2 small fw-medium border shadow-none"
                  @click="closeDeleteModal"
                  :disabled="subjectStore.isSaving"
                >
                  Batal
                </button>
                <button 
                  type="button" 
                  class="btn btn-danger w-50 rounded-3 py-2 small fw-semibold shadow-none d-flex align-items-center justify-content-center gap-2"
                  @click="confirmDelete"
                  :disabled="subjectStore.isSaving"
                >
                  <span v-if="!subjectStore.isSaving">Hapus</span>
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
import { ref, reactive, onMounted } from 'vue';
import SidebarAdmin from '../../layouts/SidebarAdmin.vue';
import { useSubjectStore } from '../../stores/subject';
import type { Subject, SubjectPayload } from '../../types/subject';

const subjectStore = useSubjectStore();

const showFormModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const selectedSubject = ref<Subject | null>(null);
const deleteErrorMessage = ref<string | null>(null);

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const form = reactive<SubjectPayload>({
  subject_code: '',
  subject_name: '',
  color_code: '#2563eb',
  category: '',
  passing_grade: 75,
  status: 'active',
});

const formErrors = reactive<Record<string, string[]>>({});

onMounted(() => {
  subjectStore.fetchSubjects();
});

const handleSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    subjectStore.fetchSubjects();
  }, 300);
};

const resetForm = () => {
  form.subject_code = '';
  form.subject_name = '';
  form.color_code = '#2563eb';
  form.category = '';
  form.passing_grade = 75;
  form.status = 'active';

  Object.keys(formErrors).forEach(key => delete formErrors[key]);
};

const openCreateModal = () => {
  resetForm();
  isEditing.value = false;
  selectedSubject.value = null;
  showFormModal.value = true;
};

const openEditModal = (subject: Subject) => {
  resetForm();
  isEditing.value = true;
  selectedSubject.value = subject;

  form.subject_code = subject.subject_code;
  form.subject_name = subject.subject_name;
  form.color_code = subject.color_code || '#2563eb';
  form.category = subject.category || '';
  form.passing_grade = subject.passing_grade ?? 75;
  form.status = subject.status;

  showFormModal.value = true;
};

const closeFormModal = () => {
  if (!subjectStore.isSaving) {
    showFormModal.value = false;
  }
};

const submitForm = async () => {
  Object.keys(formErrors).forEach(key => delete formErrors[key]);

  const payload: SubjectPayload = {
    subject_code: form.subject_code.trim().toUpperCase(),
    subject_name: form.subject_name.trim(),
    color_code: form.color_code,
    category: form.category ? form.category.trim() : '',
    passing_grade: form.passing_grade,
    status: form.status,
  };

  let result;
  if (isEditing.value && selectedSubject.value) {
    result = await subjectStore.updateSubject(selectedSubject.value.id, payload);
  } else {
    result = await subjectStore.createSubject(payload);
  }

  if (result.success) {
    showFormModal.value = false;
  } else if (result.errors) {
    Object.assign(formErrors, result.errors);
  }
};

const openDeleteModal = (subject: Subject) => {
  selectedSubject.value = subject;
  deleteErrorMessage.value = null;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  if (!subjectStore.isSaving) {
    showDeleteModal.value = false;
  }
};

const confirmDelete = async () => {
  if (!selectedSubject.value) return;

  deleteErrorMessage.value = null;
  const result = await subjectStore.deleteSubject(selectedSubject.value.id);

  if (result.success) {
    showDeleteModal.value = false;
  } else {
    deleteErrorMessage.value = result.message || 'Gagal menghapus mata pelajaran.';
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
.bg-success-subtle {
  background-color: #f0fdf4 !important;
  color: #16a34a !important;
}
.bg-secondary-subtle {
  background-color: #f1f5f9 !important;
  color: #64748b !important;
}
.fs-8 {
  font-size: 0.75rem;
}
</style>