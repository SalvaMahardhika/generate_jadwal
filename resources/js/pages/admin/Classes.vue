<template>
  <SidebarAdmin>
    <div class="container-fluid px-0">
      
      <!-- Page Header -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4 pb-2 border-bottom">
        <div>
          <h5 class="fw-bold text-dark mb-1">Manajemen Kelas</h5>
          <p class="text-secondary small mb-0">Kelola master data kelas, tingkat/jenjang, jurusan, dan penugasan wali kelas.</p>
        </div>
        <button 
          type="button" 
          class="btn btn-primary rounded-3 py-2 px-3 small fw-medium d-flex align-items-center gap-2"
          @click="openCreateModal()"
        >
          <i class="bi bi-plus-lg"></i>
          <span>Tambah Kelas</span>
        </button>
      </div>

      <!-- Alert Pesan Sukses -->
      <div v-if="classStore.successMessage" class="alert alert-success border-0 rounded-3 py-2 px-3 small d-flex align-items-center justify-content-between mb-3" role="alert">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill"></i>
          <span>{{ classStore.successMessage }}</span>
        </div>
        <button type="button" class="btn-close small" @click="classStore.successMessage = null"></button>
      </div>

      <!-- Filter Bar -->
      <div class="card border rounded-3 p-3 bg-white mb-4">
        <div class="row g-2 align-items-center">
          <!-- Search Nama Kelas -->
          <div class="col-12 col-md-3">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light border-end-0 text-muted">
                <i class="bi bi-search"></i>
              </span>
              <input 
                type="text" 
                class="form-control bg-light border-start-0 shadow-none" 
                placeholder="Cari kelas (contoh: X RPL 1)..." 
                v-model="classStore.filters.search"
                @input="classStore.fetchClasses()"
              >
            </div>
          </div>

          <!-- Filter Tahun Ajaran -->
          <div class="col-12 col-md-3">
            <select 
              class="form-select form-select-sm bg-light shadow-none" 
              v-model="classStore.filters.academic_year_id"
              @change="classStore.fetchClasses()"
            >
              <option value="">Semua Tahun Ajaran</option>
              <option v-for="ay in academicYearsList" :key="ay.id" :value="ay.id">
                {{ ay.name }} ({{ ay.semester === 'odd' ? 'Ganjil' : 'Genap' }})
              </option>
            </select>
          </div>

          <!-- Filter Tingkat / Grade -->
          <div class="col-12 col-md-3">
            <select 
              class="form-select form-select-sm bg-light shadow-none" 
              v-model="classStore.filters.grade_id"
              @change="classStore.fetchClasses()"
            >
              <option value="">Semua Tingkat</option>
              <option v-for="grade in gradesList" :key="grade.id" :value="grade.id">
                {{ grade.grade_name }}
              </option>
            </select>
          </div>

          <!-- Filter Status -->
          <div class="col-12 col-md-2">
            <select 
              class="form-select form-select-sm bg-light shadow-none" 
              v-model="classStore.filters.status"
              @change="classStore.fetchClasses()"
            >
              <option value="">Semua Status</option>
              <option value="active">Aktif</option>
              <option value="inactive">Nonaktif</option>
            </select>
          </div>

          <!-- Reset Filter Button -->
          <div class="col-12 col-md-1 text-md-end" v-if="hasActiveFilter">
            <button 
              type="button" 
              class="btn btn-link text-decoration-none text-muted small p-0 fs-8"
              @click="classStore.resetFilters()"
            >
              Reset
            </button>
          </div>
        </div>
      </div>

      <!-- Table Classes -->
      <div class="card border rounded-3 bg-white overflow-hidden shadow-sm">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="bg-light border-bottom">
              <tr>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small" style="width: 50px;">#</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Nama Kelas</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Tingkat</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Jurusan</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Tahun Ajaran</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Wali Kelas</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Status</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small text-end" style="width: 170px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="classStore.isLoading">
                <td colspan="8" class="text-center py-5 text-muted small">
                  <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                  Memuat data kelas...
                </td>
              </tr>

              <tr v-else-if="classStore.classes.length === 0">
                <td colspan="8" class="text-center py-5 text-muted small">
                  <i class="bi bi-building fs-3 d-block mb-2 text-secondary opacity-50"></i>
                  Data kelas tidak ditemukan.
                </td>
              </tr>

              <tr v-else v-for="(cls, index) in classStore.classes" :key="cls.id">
                <td class="px-3 small text-secondary">{{ index + 1 }}</td>
                <td class="px-3">
                  <div class="fw-bold text-dark small mb-0">{{ cls.class_name }}</div>
                </td>
                <td class="px-3 small">
                  <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 fs-8">
                    {{ cls.grade?.grade_name || '-' }}
                  </span>
                </td>
                <td class="px-3 small text-secondary">
                  {{ cls.major?.major_name || '-' }}
                </td>
                <td class="px-3 small text-dark font-monospace">
                  <span v-if="cls.academic_year">
                    {{ cls.academic_year.name }} ({{ cls.academic_year.semester === 'odd' ? 'Ganjil' : 'Genap' }})
                  </span>
                  <span v-else>-</span>
                </td>
                <td class="px-3 small text-secondary">
                  <div v-if="cls.homeroom_teacher" class="d-flex align-items-center gap-1.5">
                    <i class="bi bi-person-badge text-primary fs-7"></i>
                    <span>{{ cls.homeroom_teacher.name }}</span>
                  </div>
                  <span v-else class="text-muted fst-italic">Belum ditentukan</span>
                </td>
                <td class="px-3 small">
                  <span 
                    class="badge rounded-pill px-2.5 py-1 fs-8 fw-semibold"
                    :class="cls.status === 'active' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle'"
                  >
                    {{ cls.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="px-3 text-end">
                  <div class="d-flex justify-content-end gap-1">
                    <router-link 
                      :to="{ name: 'admin.class-subjects', query: { class_id: cls.id } }"
                      class="btn btn-light btn-sm text-primary border-0 rounded-2 px-2 py-1"
                      title="Plotting Mapel & Guru"
                    >
                      <i class="bi bi-journal-bookmark-fill fs-7 me-1"></i>
                      <span class="fs-8 fw-medium">Mapel</span>
                    </router-link>

                    <button 
                      type="button" 
                      class="btn btn-light btn-sm text-secondary border-0 rounded-2 px-2 py-1"
                      title="Edit Kelas"
                      @click="openEditModal(cls)"
                    >
                      <i class="bi bi-pencil-square fs-7"></i>
                    </button>
                    <button 
                      type="button" 
                      class="btn btn-light btn-sm text-danger border-0 rounded-2 px-2 py-1"
                      title="Hapus Kelas"
                      @click="openDeleteModal(cls)"
                    >
                      <i class="bi bi-trash fs-7"></i>
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
              <h6 class="modal-title fw-bold text-dark">{{ isEditing ? 'Edit Data Kelas' : 'Tambah Kelas Baru' }}</h6>
              <button type="button" class="btn-close small shadow-none" @click="closeFormModal" :disabled="classStore.isSaving"></button>
            </div>

            <form @submit.prevent="submitForm">
              <div class="modal-body p-4">
                
                <!-- Nama Kelas -->
                <div class="mb-3">
                  <label for="class_name" class="form-label fw-medium text-dark small">Nama Kelas <span class="text-danger">*</span></label>
                  <input 
                    type="text" 
                    id="class_name" 
                    class="form-control form-control-sm rounded-3 shadow-none"
                    :class="{ 'is-invalid': formErrors.class_name }"
                    placeholder="Contoh: X RPL 1 atau 10 IPA 2"
                    v-model="form.class_name"
                    required
                  >
                  <div v-if="formErrors.class_name" class="invalid-feedback small">{{ formErrors.class_name[0] }}</div>
                </div>

                <!-- Tingkat & Jurusan -->
                <div class="row g-3 mb-3">
                  <div class="col-12 col-sm-6">
                    <label for="grade_id" class="form-label fw-medium text-dark small">Tingkat / Grade <span class="text-danger">*</span></label>
                    <select 
                      id="grade_id" 
                      class="form-select form-select-sm rounded-3 shadow-none"
                      :class="{ 'is-invalid': formErrors.grade_id }"
                      v-model="form.grade_id"
                      required
                    >
                      <option value="">-- Pilih Tingkat --</option>
                      <option v-for="grade in gradesList" :key="grade.id" :value="grade.id">
                        {{ grade.grade_name }}
                      </option>
                    </select>
                    <div v-if="formErrors.grade_id" class="invalid-feedback small">{{ formErrors.grade_id[0] }}</div>
                  </div>

                  <div class="col-12 col-sm-6">
                    <label for="major_id" class="form-label fw-medium text-dark small">Jurusan (Opsional)</label>
                    <select 
                      id="major_id" 
                      class="form-select form-select-sm rounded-3 shadow-none"
                      :class="{ 'is-invalid': formErrors.major_id }"
                      v-model="form.major_id"
                    >
                      <option value="">-- Tanpa Jurusan --</option>
                      <option v-for="major in majorsList" :key="major.id" :value="major.id">
                        {{ major.major_name }}
                      </option>
                    </select>
                    <div v-if="formErrors.major_id" class="invalid-feedback small">{{ formErrors.major_id[0] }}</div>
                  </div>
                </div>

                <!-- Tahun Ajaran & Wali Kelas -->
                <div class="row g-3 mb-3">
                  <div class="col-12 col-sm-6">
                    <label for="academic_year_id" class="form-label fw-medium text-dark small">Tahun Ajaran <span class="text-danger">*</span></label>
                    <select 
                      id="academic_year_id" 
                      class="form-select form-select-sm rounded-3 shadow-none"
                      :class="{ 'is-invalid': formErrors.academic_year_id }"
                      v-model="form.academic_year_id"
                      required
                    >
                      <option value="">-- Pilih Tahun Ajaran --</option>
                      <option v-for="ay in academicYearsList" :key="ay.id" :value="ay.id">
                        {{ ay.name }} ({{ ay.semester === 'odd' ? 'Ganjil' : 'Genap' }})
                      </option>
                    </select>
                    <div v-if="formErrors.academic_year_id" class="invalid-feedback small">{{ formErrors.academic_year_id[0] }}</div>
                  </div>

                  <div class="col-12 col-sm-6">
                    <label for="homeroom_teacher_id" class="form-label fw-medium text-dark small">Wali Kelas (Opsional)</label>
                    <select 
                      id="homeroom_teacher_id" 
                      class="form-select form-select-sm rounded-3 shadow-none"
                      :class="{ 'is-invalid': formErrors.homeroom_teacher_id }"
                      v-model="form.homeroom_teacher_id"
                    >
                      <option value="">-- Pilih Wali Kelas --</option>
                      <option v-for="teacher in teachersList" :key="teacher.id" :value="teacher.id">
                        {{ teacher.name }}
                      </option>
                    </select>
                    <div v-if="formErrors.homeroom_teacher_id" class="invalid-feedback small">{{ formErrors.homeroom_teacher_id[0] }}</div>
                  </div>
                </div>

                <!-- Status Kelas -->
                <div class="mb-2">
                  <label for="status" class="form-label fw-medium text-dark small">Status Kelas</label>
                  <select 
                    id="status" 
                    class="form-select form-select-sm rounded-3 shadow-none"
                    :class="{ 'is-invalid': formErrors.status }"
                    v-model="form.status"
                  >
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                  </select>
                  <div v-if="formErrors.status" class="invalid-feedback small">{{ formErrors.status[0] }}</div>
                </div>

              </div>

              <div class="modal-footer border-top px-4 py-3 bg-light">
                <button 
                  type="button" 
                  class="btn btn-light rounded-3 py-2 px-3 small fw-medium border shadow-none"
                  @click="closeFormModal"
                  :disabled="classStore.isSaving"
                >
                  Batal
                </button>
                <button 
                  type="submit" 
                  class="btn btn-primary rounded-3 py-2 px-3 small fw-semibold shadow-none d-flex align-items-center gap-2"
                  :disabled="classStore.isSaving"
                >
                  <span v-if="!classStore.isSaving">Simpan</span>
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
              
              <h6 class="fw-bold text-dark mb-1">Hapus Kelas?</h6>
              <p class="text-secondary small mb-3">
                Apakah Anda yakin ingin menghapus kelas <strong>{{ selectedClass?.class_name }}</strong>?
              </p>

              <div v-if="deleteErrorMessage" class="alert alert-danger border-0 rounded-3 py-2 px-3 small text-start mb-3" role="alert">
                {{ deleteErrorMessage }}
              </div>

              <div class="d-flex gap-2">
                <button 
                  type="button" 
                  class="btn btn-light w-50 rounded-3 py-2 small fw-medium border shadow-none"
                  @click="closeDeleteModal"
                  :disabled="classStore.isSaving"
                >
                  Batal
                </button>
                <button 
                  type="button" 
                  class="btn btn-danger w-50 rounded-3 py-2 small fw-semibold shadow-none d-flex align-items-center justify-content-center gap-2"
                  @click="confirmDelete"
                  :disabled="classStore.isSaving"
                >
                  <span v-if="!classStore.isSaving">Hapus</span>
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
import { useClassStore } from '../../stores/class';
import api from '../../services/api';
import type { ClassItem, ClassPayload, GradeOption, TeacherOption } from '../../types/class';
import type { AcademicYear } from '../../types/academicYear';
import type { Major } from '../../types/major';

const classStore = useClassStore();

const showFormModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const selectedClass = ref<ClassItem | null>(null);
const deleteErrorMessage = ref<string | null>(null);

// Options List untuk Dropdown Form & Filter
const gradesList = ref<GradeOption[]>([]);
const majorsList = ref<Major[]>([]);
const academicYearsList = ref<AcademicYear[]>([]);
const teachersList = ref<TeacherOption[]>([]);

const form = reactive<ClassPayload>({
  class_name: '',
  grade_id: '',
  major_id: '',
  academic_year_id: '',
  homeroom_teacher_id: '',
  status: 'active',
});

const formErrors = reactive<Record<string, string[]>>({});

const hasActiveFilter = computed(() => {
  return Boolean(
    classStore.filters.search || 
    classStore.filters.academic_year_id || 
    classStore.filters.grade_id || 
    classStore.filters.status
  );
});

// Load Options untuk Select
const fetchDropdownOptions = async () => {
  try {
    const [gradesRes, majorsRes, ayRes, teachersRes] = await Promise.all([
      api.get('/grades'),
      api.get('/majors'),
      api.get('/academic-years'),
      api.get('/teachers'),
    ]);

    if (gradesRes.data?.status === 'success') gradesList.value = gradesRes.data.data;
    if (majorsRes.data?.status === 'success') majorsList.value = majorsRes.data.data;
    if (ayRes.data?.status === 'success') {
      academicYearsList.value = ayRes.data.data;
      // Auto-select Tahun Ajaran Aktif jika form baru ditambahkan
      const activeAY = academicYearsList.value.find(a => a.is_active);
      if (activeAY && !form.academic_year_id) {
        form.academic_year_id = activeAY.id;
      }
    }
    if (teachersRes.data?.status === 'success') teachersList.value = teachersRes.data.data;
  } catch (err) {
    console.error('Gagal memuat opsi pilihan kelas:', err);
  }
};

onMounted(() => {
  classStore.fetchClasses();
  fetchDropdownOptions();
});

const resetForm = () => {
  const activeAY = academicYearsList.value.find(a => a.is_active);

  form.class_name = '';
  form.grade_id = '';
  form.major_id = '';
  form.academic_year_id = activeAY ? activeAY.id : '';
  form.homeroom_teacher_id = '';
  form.status = 'active';

  Object.keys(formErrors).forEach(key => delete formErrors[key]);
};

const openCreateModal = () => {
  resetForm();
  isEditing.value = false;
  selectedClass.value = null;
  showFormModal.value = true;
};

const openEditModal = (cls: ClassItem) => {
  resetForm();
  isEditing.value = true;
  selectedClass.value = cls;

  form.class_name = cls.class_name;
  form.grade_id = cls.grade_id;
  form.major_id = cls.major_id || '';
  form.academic_year_id = cls.academic_year_id;
  form.homeroom_teacher_id = cls.homeroom_teacher_id || '';
  form.status = cls.status;

  showFormModal.value = true;
};

const closeFormModal = () => {
  if (!classStore.isSaving) {
    showFormModal.value = false;
  }
};

const submitForm = async () => {
  Object.keys(formErrors).forEach(key => delete formErrors[key]);

  const payload: ClassPayload = {
    class_name: form.class_name.trim(),
    grade_id: Number(form.grade_id),
    major_id: form.major_id ? Number(form.major_id) : null,
    academic_year_id: Number(form.academic_year_id),
    homeroom_teacher_id: form.homeroom_teacher_id ? Number(form.homeroom_teacher_id) : null,
    status: form.status,
  };

  let result;
  if (isEditing.value && selectedClass.value) {
    result = await classStore.updateClass(selectedClass.value.id, payload);
  } else {
    result = await classStore.createClass(payload);
  }

  if (result.success) {
    showFormModal.value = false;
  } else if (result.errors) {
    Object.assign(formErrors, result.errors);
  }
};

const openDeleteModal = (cls: ClassItem) => {
  selectedClass.value = cls;
  deleteErrorMessage.value = null;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  if (!classStore.isSaving) {
    showDeleteModal.value = false;
  }
};

const confirmDelete = async () => {
  if (!selectedClass.value) return;

  deleteErrorMessage.value = null;
  const result = await classStore.deleteClass(selectedClass.value.id);

  if (result.success) {
    showDeleteModal.value = false;
  } else {
    deleteErrorMessage.value = result.message || 'Gagal menghapus kelas.';
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
  background-color: #f8fafc !important;
  color: #64748b !important;
}
.border-success-subtle {
  border-color: #bbf7d0 !important;
}
.border-secondary-subtle {
  border-color: #e2e8f0 !important;
}
.fs-7 {
  font-size: 0.82rem;
}
.fs-8 {
  font-size: 0.72rem;
}
</style>