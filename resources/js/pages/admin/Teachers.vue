<template>
  <SidebarAdmin>
    <div class="container-fluid px-0">
      
      <!-- Page Header -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4 pb-2 border-bottom">
        <div>
          <h5 class="fw-bold text-dark mb-1">Data Guru</h5>
          <p class="text-secondary small mb-0">Kelola informasi pengajar, NIP, beban JP per minggu, dan status keaktifan.</p>
        </div>
        <button 
          type="button" 
          class="btn btn-primary rounded-3 py-2 px-3 small fw-medium d-flex align-items-center gap-2"
          @click="openCreateModal"
        >
          <i class="bi bi-plus-lg"></i>
          <span>Tambah Guru</span>
        </button>
      </div>

      <!-- Alert Pesan Sukses / Error Global -->
      <div v-if="teacherStore.successMessage" class="alert alert-success border-0 rounded-3 py-2 px-3 small d-flex align-items-center justify-content-between mb-3" role="alert">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill"></i>
          <span>{{ teacherStore.successMessage }}</span>
        </div>
        <button type="button" class="btn-close small" @click="teacherStore.successMessage = null"></button>
      </div>

      <!-- Filter & Search Bar -->
      <div class="card border rounded-3 p-3 bg-white mb-3">
        <div class="row g-2 align-items-center">
          <div class="col-12 col-md-6 col-lg-4">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
              <input 
                type="text" 
                class="form-control bg-light border-start-0 shadow-none" 
                placeholder="Cari nama atau NIP..."
                v-model="teacherStore.filters.search"
                @input="handleSearch"
              >
            </div>
          </div>
          <div class="col-12 col-md-4 col-lg-3">
            <select 
              class="form-select form-select-sm bg-light shadow-none" 
              v-model="teacherStore.filters.status"
              @change="teacherStore.fetchTeachers()"
            >
              <option value="">Semua Status</option>
              <option value="active">Aktif</option>
              <option value="inactive">Tidak Aktif</option>
            </select>
          </div>
          <div class="col-12 col-md-2 ms-auto text-end" v-if="teacherStore.filters.search || teacherStore.filters.status">
            <button 
              type="button" 
              class="btn btn-link text-decoration-none text-muted small p-0"
              @click="teacherStore.resetFilters()"
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
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">NIP</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Nama Guru</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Gender</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Max JP/Minggu</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Status</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small text-end" style="width: 120px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <!-- Loading State -->
              <tr v-if="teacherStore.isLoading">
                <td colspan="7" class="text-center py-5 text-muted small">
                  <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                  Memuat data guru...
                </td>
              </tr>

              <!-- Empty State -->
              <tr v-else-if="teacherStore.teachers.length === 0">
                <td colspan="7" class="text-center py-5 text-muted small">
                  <i class="bi bi-inbox fs-3 d-block mb-2 text-secondary"></i>
                  Data guru tidak ditemukan.
                </td>
              </tr>

              <!-- Data Rows -->
              <tr v-else v-for="(teacher, index) in teacherStore.teachers" :key="teacher.id">
                <td class="px-3 small text-secondary">{{ index + 1 }}</td>
                <td class="px-3 small font-monospace text-dark">{{ teacher.nip || '-' }}</td>
                <td class="px-3 small fw-medium text-dark">{{ teacher.name }}</td>
                <td class="px-3 small">
                  <span 
                    class="badge rounded-pill px-2 py-1 fw-normal fs-8"
                    :class="teacher.gender === 'L' ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger'"
                  >
                    {{ teacher.gender === 'L' ? 'Laki-Laki' : 'Perempuan' }}
                  </span>
                </td>
                <td class="px-3 small fw-semibold text-dark">{{ teacher.max_jp_per_week }} JP</td>
                <td class="px-3 small">
                  <span 
                    class="badge rounded-pill px-2 py-1 fw-normal fs-8"
                    :class="teacher.status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'"
                  >
                    {{ teacher.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                  </span>
                </td>
                <td class="px-3 text-end">
                  <div class="d-flex justify-content-end gap-1">
                    <button 
                      type="button" 
                      class="btn btn-light btn-sm text-secondary border-0 rounded-2 px-2 py-1"
                      title="Edit"
                      @click="openEditModal(teacher)"
                    >
                      <i class="bi bi-pencil-square"></i>
                    </button>
                    <button 
                      type="button" 
                      class="btn btn-light btn-sm text-danger border-0 rounded-2 px-2 py-1"
                      title="Hapus"
                      @click="openDeleteModal(teacher)"
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

    <!-- Modal Form (Tambah / Edit Guru) -->
    <Teleport to="body">
      <div v-if="showFormModal" class="modal-backdrop fade show"></div>
      <div 
        v-if="showFormModal" 
        class="modal fade show d-block" 
        tabindex="-1"
        @click.self="closeFormModal"
      >
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            
            <div class="modal-header border-bottom px-4 py-3">
              <h6 class="modal-title fw-bold text-dark">{{ isEditing ? 'Edit Data Guru' : 'Tambah Guru Baru' }}</h6>
              <button type="button" class="btn-close small shadow-none" @click="closeFormModal" :disabled="teacherStore.isSaving"></button>
            </div>

            <form @submit.prevent="submitForm">
              <div class="modal-body p-4">
                
                <!-- NIP -->
                <div class="mb-3">
                  <label for="nip" class="form-label fw-medium text-dark small">NIP (Opsional)</label>
                  <input 
                    type="text" 
                    id="nip" 
                    class="form-control form-control-sm rounded-3 shadow-none"
                    :class="{ 'is-invalid': formErrors.nip }"
                    placeholder="Contoh: 198501012010011001"
                    v-model="form.nip"
                  >
                  <div v-if="formErrors.nip" class="invalid-feedback small">{{ formErrors.nip[0] }}</div>
                </div>

                <!-- Nama Lengkap -->
                <div class="mb-3">
                  <label for="name" class="form-label fw-medium text-dark small">Nama Lengkap Guru <span class="text-danger">*</span></label>
                  <input 
                    type="text" 
                    id="name" 
                    class="form-control form-control-sm rounded-3 shadow-none"
                    :class="{ 'is-invalid': formErrors.name }"
                    placeholder="Contoh: Drs. Budi Santoso, M.Pd"
                    v-model="form.name"
                    required
                  >
                  <div v-if="formErrors.name" class="invalid-feedback small">{{ formErrors.name[0] }}</div>
                </div>

                <!-- Gender & Max JP per Minggu -->
                <div class="row g-3 mb-3">
                  <div class="col-12 col-sm-6">
                    <label class="form-label fw-medium text-dark small">Jenis Kelamin <span class="text-danger">*</span></label>
                    <select 
                      class="form-select form-select-sm rounded-3 shadow-none"
                      :class="{ 'is-invalid': formErrors.gender }"
                      v-model="form.gender"
                      required
                    >
                      <option value="L">Laki-Laki</option>
                      <option value="P">Perempuan</option>
                    </select>
                    <div v-if="formErrors.gender" class="invalid-feedback small">{{ formErrors.gender[0] }}</div>
                  </div>

                  <div class="col-12 col-sm-6">
                    <label for="max_jp" class="form-label fw-medium text-dark small">Max JP/Minggu <span class="text-danger">*</span></label>
                    <input 
                      type="number" 
                      id="max_jp" 
                      min="1"
                      class="form-control form-control-sm rounded-3 shadow-none"
                      :class="{ 'is-invalid': formErrors.max_jp_per_week }"
                      v-model.number="form.max_jp_per_week"
                      required
                    >
                    <div v-if="formErrors.max_jp_per_week" class="invalid-feedback small">{{ formErrors.max_jp_per_week[0] }}</div>
                  </div>
                </div>

                <!-- Status -->
                <div class="mb-2">
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

              <div class="modal-footer border-top px-4 py-3 bg-light">
                <button 
                  type="button" 
                  class="btn btn-light rounded-3 py-2 px-3 small fw-medium border shadow-none"
                  @click="closeFormModal"
                  :disabled="teacherStore.isSaving"
                >
                  Batal
                </button>
                <button 
                  type="submit" 
                  class="btn btn-primary rounded-3 py-2 px-3 small fw-semibold shadow-none d-flex align-items-center gap-2"
                  :disabled="teacherStore.isSaving"
                >
                  <span v-if="!teacherStore.isSaving">Simpan</span>
                  <span v-else class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                </button>
              </div>
            </form>

          </div>
        </div>
      </div>
    </Teleport>

    <!-- Modal Konfirmasi Hapus -->
    <Teleport to="body">
      <div v-if="showDeleteModal" class="modal-backdrop fade show"></div>
      <div 
        v-if="showDeleteModal" 
        class="modal fade show d-block" 
        tabindex="-1"
        @click.self="closeDeleteModal"
      >
        <div class="modal-dialog modal-dialog-centered modal-sm">
          <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="modal-body text-center p-4">
              
              <div class="delete-icon mx-auto mb-3 text-danger bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-exclamation-triangle fs-4"></i>
              </div>
              
              <h6 class="fw-bold text-dark mb-1">Hapus Data Guru?</h6>
              <p class="text-secondary small mb-3">
                Apakah Anda yakin ingin menghapus <strong>{{ selectedTeacher?.name }}</strong>?
              </p>

              <!-- Pesan Error Penghapusan dari Backend -->
              <div v-if="deleteErrorMessage" class="alert alert-danger border-0 rounded-3 py-2 px-3 small text-start mb-3" role="alert">
                {{ deleteErrorMessage }}
              </div>

              <div class="d-flex gap-2">
                <button 
                  type="button" 
                  class="btn btn-light w-50 rounded-3 py-2 small fw-medium border shadow-none"
                  @click="closeDeleteModal"
                  :disabled="teacherStore.isSaving"
                >
                  Batal
                </button>
                <button 
                  type="button" 
                  class="btn btn-danger w-50 rounded-3 py-2 small fw-semibold shadow-none d-flex align-items-center justify-content-center gap-2"
                  @click="confirmDelete"
                  :disabled="teacherStore.isSaving"
                >
                  <span v-if="!teacherStore.isSaving">Hapus</span>
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
import { useTeacherStore } from '../../stores/teacher';
import type { Teacher, TeacherPayload } from '../../types/teacher';

const teacherStore = useTeacherStore();

const showFormModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const selectedTeacher = ref<Teacher | null>(null);
const deleteErrorMessage = ref<string | null>(null);

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const form = reactive<TeacherPayload>({
  nip: '',
  name: '',
  gender: 'L',
  max_jp_per_week: 24,
  status: 'active',
});

const formErrors = reactive<Record<string, string[]>>({});

onMounted(() => {
  teacherStore.fetchTeachers();
});

const handleSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    teacherStore.fetchTeachers();
  }, 300);
};

const resetForm = () => {
  form.nip = '';
  form.name = '';
  form.gender = 'L';
  form.max_jp_per_week = 24;
  form.status = 'active';
  
  Object.keys(formErrors).forEach(key => delete formErrors[key]);
};

const openCreateModal = () => {
  resetForm();
  isEditing.value = false;
  selectedTeacher.value = null;
  showFormModal.value = true;
};

const openEditModal = (teacher: Teacher) => {
  resetForm();
  isEditing.value = true;
  selectedTeacher.value = teacher;

  form.nip = teacher.nip || '';
  form.name = teacher.name;
  form.gender = teacher.gender;
  form.max_jp_per_week = teacher.max_jp_per_week;
  form.status = teacher.status;

  showFormModal.value = true;
};

const closeFormModal = () => {
  if (!teacherStore.isSaving) {
    showFormModal.value = false;
  }
};

const submitForm = async () => {
  Object.keys(formErrors).forEach(key => delete formErrors[key]);

  const payload: TeacherPayload = {
    nip: form.nip ? form.nip.trim() : null,
    name: form.name.trim(),
    gender: form.gender,
    max_jp_per_week: form.max_jp_per_week,
    status: form.status,
  };

  let result;
  if (isEditing.value && selectedTeacher.value) {
    result = await teacherStore.updateTeacher(selectedTeacher.value.id, payload);
  } else {
    result = await teacherStore.createTeacher(payload);
  }

  if (result.success) {
    showFormModal.value = false;
  } else if (result.errors) {
    Object.assign(formErrors, result.errors);
  }
};

const openDeleteModal = (teacher: Teacher) => {
  selectedTeacher.value = teacher;
  deleteErrorMessage.value = null;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  if (!teacherStore.isSaving) {
    showDeleteModal.value = false;
  }
};

const confirmDelete = async () => {
  if (!selectedTeacher.value) return;

  deleteErrorMessage.value = null;
  const result = await teacherStore.deleteTeacher(selectedTeacher.value.id);

  if (result.success) {
    showDeleteModal.value = false;
  } else {
    deleteErrorMessage.value = result.message || 'Gagal menghapus data guru.';
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