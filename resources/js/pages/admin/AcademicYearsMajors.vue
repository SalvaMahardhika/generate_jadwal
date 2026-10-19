<template>
  <SidebarAdmin>
    <div class="container-fluid px-0">
      
      <!-- Header Halaman -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4 pb-2 border-bottom">
        <div>
          <h5 class="fw-bold text-dark mb-1">Tahun Ajaran & Jurusan</h5>
          <p class="text-secondary small mb-0">Kelola master data tahun ajaran aktif dan daftar jurusan sekolah.</p>
        </div>

        <button 
          v-if="activeTab === 'academic_years'"
          type="button" 
          class="btn btn-primary rounded-3 py-2 px-3 small fw-medium d-flex align-items-center gap-2"
          @click="openAcademicYearModal()"
        >
          <i class="bi bi-plus-lg"></i>
          <span>Tambah Tahun Ajaran</span>
        </button>

        <button 
          v-else
          type="button" 
          class="btn btn-primary rounded-3 py-2 px-3 small fw-medium d-flex align-items-center gap-2"
          @click="openMajorModal()"
        >
          <i class="bi bi-plus-lg"></i>
          <span>Tambah Jurusan</span>
        </button>
      </div>

      <!-- Alert Pesan Sukses -->
      <div v-if="currentSuccessMessage" class="alert alert-success border-0 rounded-3 py-2 px-3 small d-flex align-items-center justify-content-between mb-3" role="alert">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill"></i>
          <span>{{ currentSuccessMessage }}</span>
        </div>
        <button type="button" class="btn-close small" @click="clearSuccessMessage()"></button>
      </div>

      <!-- Navigasi Tab -->
      <ul class="nav nav-tabs border-bottom-0 mb-3">
        <li class="nav-item">
          <button 
            class="nav-link fw-semibold px-4 py-2.5 rounded-top-3 border-bottom-0"
            :class="{ 'active text-primary bg-white border': activeTab === 'academic_years', 'text-muted bg-light': activeTab !== 'academic_years' }"
            @click="activeTab = 'academic_years'"
          >
            <i class="bi bi-calendar-range me-2"></i>Tahun Ajaran
          </button>
        </li>
        <li class="nav-item">
          <button 
            class="nav-link fw-semibold px-4 py-2.5 rounded-top-3 border-bottom-0"
            :class="{ 'active text-primary bg-white border': activeTab === 'majors', 'text-muted bg-light': activeTab !== 'majors' }"
            @click="activeTab = 'majors'"
          >
            <i class="bi bi-mortarboard me-2"></i>Jurusan
          </button>
        </li>
      </ul>

      <!-- TAB 1: TAHUN AJARAN -->
      <div v-if="activeTab === 'academic_years'">
        <!-- Filter Search Bar -->
        <div class="card border rounded-3 p-3 bg-white mb-3">
          <div class="row g-2 align-items-center">
            <div class="col-12 col-md-6">
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0 text-muted">
                  <i class="bi bi-search"></i>
                </span>
                <input 
                  type="text" 
                  class="form-control bg-light border-start-0 shadow-none" 
                  placeholder="Cari tahun ajaran (contoh: 2026/2027)..." 
                  v-model="academicYearStore.searchQuery"
                  @input="academicYearStore.fetchAcademicYears()"
                >
              </div>
            </div>
          </div>
        </div>

        <!-- Table Tahun Ajaran -->
        <div class="card border rounded-3 bg-white overflow-hidden">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light border-bottom">
                <tr>
                  <th scope="col" class="py-3 px-3 text-muted fw-semibold small" style="width: 50px;">#</th>
                  <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Tahun Ajaran</th>
                  <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Semester</th>
                  <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Rentang Tanggal</th>
                  <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Status Active</th>
                  <th scope="col" class="py-3 px-3 text-muted fw-semibold small text-end" style="width: 140px;">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="academicYearStore.isLoading">
                  <td colspan="6" class="text-center py-5 text-muted small">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    Memuat data tahun ajaran...
                  </td>
                </tr>

                <tr v-else-if="academicYearStore.academicYears.length === 0">
                  <td colspan="6" class="text-center py-5 text-muted small">
                    <i class="bi bi-calendar-x fs-3 d-block mb-2 text-secondary"></i>
                    Data tahun ajaran tidak ditemukan.
                  </td>
                </tr>

                <tr v-else v-for="(ay, index) in academicYearStore.academicYears" :key="ay.id">
                  <td class="px-3 small text-secondary">{{ index + 1 }}</td>
                  <td class="px-3 small fw-bold text-dark">{{ ay.name }}</td>
                  <td class="px-3 small">
                    <span 
                      class="badge rounded-pill px-2.5 py-1 fs-8 fw-semibold"
                      :class="ay.semester === 'odd' ? 'bg-info-subtle text-info border border-info-subtle' : 'bg-purple-subtle text-purple border border-purple-subtle'"
                    >
                      {{ ay.semester === 'odd' ? 'Ganjil' : 'Genap' }}
                    </span>
                  </td>
                  <td class="px-3 small text-dark font-monospace">
                    {{ formatDate(ay.start_date) }} - {{ formatDate(ay.end_date) }}
                  </td>
                  <td class="px-3 small">
                    <span 
                      class="badge rounded-pill px-2.5 py-1 fs-8 fw-semibold"
                      :class="ay.is_active ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle'"
                    >
                      {{ ay.is_active ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                  </td>
                  <td class="px-3 text-end">
                    <div class="d-flex justify-content-end gap-1">
                      <button 
                        v-if="!ay.is_active"
                        type="button" 
                        class="btn btn-light btn-sm text-success border-0 rounded-2 px-2 py-1"
                        title="Set Aktif"
                        @click="handleSetActive(ay)"
                      >
                        <i class="bi bi-check-circle fs-7"></i>
                      </button>
                      <button 
                        type="button" 
                        class="btn btn-light btn-sm text-secondary border-0 rounded-2 px-2 py-1"
                        title="Edit"
                        @click="openAcademicYearModal(ay)"
                      >
                        <i class="bi bi-pencil-square fs-7"></i>
                      </button>
                      <button 
                        type="button" 
                        class="btn btn-light btn-sm text-danger border-0 rounded-2 px-2 py-1"
                        title="Hapus"
                        :disabled="ay.is_active"
                        @click="openDeleteAcademicYearModal(ay)"
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

      <!-- TAB 2: JURUSAN -->
      <div v-else-if="activeTab === 'majors'">
        <!-- Filter Bar -->
        <div class="card border rounded-3 p-3 bg-white mb-3">
          <div class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0 text-muted">
                  <i class="bi bi-search"></i>
                </span>
                <input 
                  type="text" 
                  class="form-control bg-light border-start-0 shadow-none" 
                  placeholder="Cari nama jurusan (IPA, RPL, dll)..." 
                  v-model="majorStore.filters.search"
                  @input="majorStore.fetchMajors()"
                >
              </div>
            </div>

            <div class="col-12 col-md-4">
              <select 
                class="form-select form-select-sm bg-light shadow-none"
                v-model="majorStore.filters.status"
                @change="majorStore.fetchMajors()"
              >
                <option value="">Semua Status</option>
                <option value="active">Aktif</option>
                <option value="inactive">Nonaktif</option>
              </select>
            </div>

            <div class="col-12 col-md-3 text-md-end" v-if="majorStore.filters.search || majorStore.filters.status">
              <button 
                type="button" 
                class="btn btn-link text-decoration-none text-muted small p-0"
                @click="majorStore.resetFilters()"
              >
                Reset Filter
              </button>
            </div>
          </div>
        </div>

        <!-- Table Jurusan -->
        <div class="card border rounded-3 bg-white overflow-hidden">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light border-bottom">
                <tr>
                  <th scope="col" class="py-3 px-3 text-muted fw-semibold small" style="width: 50px;">#</th>
                  <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Nama Jurusan</th>
                  <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Status</th>
                  <th scope="col" class="py-3 px-3 text-muted fw-semibold small text-end" style="width: 120px;">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="majorStore.isLoading">
                  <td colspan="4" class="text-center py-5 text-muted small">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    Memuat data jurusan...
                  </td>
                </tr>

                <tr v-else-if="majorStore.majors.length === 0">
                  <td colspan="4" class="text-center py-5 text-muted small">
                    <i class="bi bi-mortarboard fs-3 d-block mb-2 text-secondary"></i>
                    Data jurusan tidak ditemukan.
                  </td>
                </tr>

                <tr v-else v-for="(major, index) in majorStore.majors" :key="major.id">
                  <td class="px-3 small text-secondary">{{ index + 1 }}</td>
                  <td class="px-3 small fw-bold text-dark">{{ major.major_name }}</td>
                  <td class="px-3 small">
                    <span 
                      class="badge rounded-pill px-2.5 py-1 fs-8 fw-semibold"
                      :class="major.status === 'active' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle'"
                    >
                      {{ major.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                    </span>
                  </td>
                  <td class="px-3 text-end">
                    <div class="d-flex justify-content-end gap-1">
                      <button 
                        type="button" 
                        class="btn btn-light btn-sm text-secondary border-0 rounded-2 px-2 py-1"
                        title="Edit"
                        @click="openMajorModal(major)"
                      >
                        <i class="bi bi-pencil-square fs-7"></i>
                      </button>
                      <button 
                        type="button" 
                        class="btn btn-light btn-sm text-danger border-0 rounded-2 px-2 py-1"
                        title="Hapus"
                        @click="openDeleteMajorModal(major)"
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

    </div>

    <!-- MODAL FORM TAHUN AJARAN -->
    <Teleport to="body">
      <div v-if="showAcademicYearModal" class="modal-backdrop fade show"></div>
      <div v-if="showAcademicYearModal" class="modal fade show d-block" tabindex="-1" @click.self="closeAcademicYearModal">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            
            <div class="modal-header border-bottom px-4 py-3">
              <h6 class="modal-title fw-bold text-dark">{{ isEditingAY ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran Baru' }}</h6>
              <button type="button" class="btn-close small shadow-none" @click="closeAcademicYearModal" :disabled="academicYearStore.isSaving"></button>
            </div>

            <form @submit.prevent="submitAcademicYear">
              <div class="modal-body p-4">
                
                <div class="mb-3">
                  <label for="ay_name" class="form-label fw-medium text-dark small">Nama Tahun Ajaran <span class="text-danger">*</span></label>
                  <input 
                    type="text" 
                    id="ay_name" 
                    class="form-control form-control-sm rounded-3 shadow-none"
                    :class="{ 'is-invalid': ayErrors.name }"
                    placeholder="Contoh: 2026/2027"
                    v-model="ayForm.name"
                    required
                  >
                  <div v-if="ayErrors.name" class="invalid-feedback small">{{ ayErrors.name[0] }}</div>
                </div>

                <div class="mb-3">
                  <label for="semester" class="form-label fw-medium text-dark small">Semester <span class="text-danger">*</span></label>
                  <select 
                    id="semester" 
                    class="form-select form-select-sm rounded-3 shadow-none"
                    :class="{ 'is-invalid': ayErrors.semester }"
                    v-model="ayForm.semester"
                    required
                  >
                    <option value="odd">Ganjil</option>
                    <option value="even">Genap</option>
                  </select>
                  <div v-if="ayErrors.semester" class="invalid-feedback small">{{ ayErrors.semester[0] }}</div>
                </div>

                <div class="row g-3 mb-3">
                  <div class="col-12 col-sm-6">
                    <label for="start_date" class="form-label fw-medium text-dark small">Tanggal Mulai <span class="text-danger">*</span></label>
                    <input 
                      type="date" 
                      id="start_date" 
                      class="form-control form-control-sm rounded-3 shadow-none"
                      :class="{ 'is-invalid': ayErrors.start_date }"
                      v-model="ayForm.start_date"
                      required
                    >
                    <div v-if="ayErrors.start_date" class="invalid-feedback small">{{ ayErrors.start_date[0] }}</div>
                  </div>

                  <div class="col-12 col-sm-6">
                    <label for="end_date" class="form-label fw-medium text-dark small">Tanggal Selesai <span class="text-danger">*</span></label>
                    <input 
                      type="date" 
                      id="end_date" 
                      class="form-control form-control-sm rounded-3 shadow-none"
                      :class="{ 'is-invalid': ayErrors.end_date }"
                      v-model="ayForm.end_date"
                      required
                    >
                    <div v-if="ayErrors.end_date" class="invalid-feedback small">{{ ayErrors.end_date[0] }}</div>
                  </div>
                </div>

                <div class="form-check form-switch mb-2">
                  <input 
                    class="form-check-input shadow-none" 
                    type="checkbox" 
                    id="is_active"
                    v-model="ayForm.is_active"
                  >
                  <label class="form-check-label small fw-medium text-dark" for="is_active">
                    Set sebagai Tahun Ajaran Aktif
                  </label>
                </div>

              </div>

              <div class="modal-footer border-top px-4 py-3 bg-light">
                <button 
                  type="button" 
                  class="btn btn-light rounded-3 py-2 px-3 small fw-medium border shadow-none"
                  @click="closeAcademicYearModal"
                  :disabled="academicYearStore.isSaving"
                >
                  Batal
                </button>
                <button 
                  type="submit" 
                  class="btn btn-primary rounded-3 py-2 px-3 small fw-semibold shadow-none d-flex align-items-center gap-2"
                  :disabled="academicYearStore.isSaving"
                >
                  <span v-if="!academicYearStore.isSaving">Simpan</span>
                  <span v-else class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                </button>
              </div>
            </form>

          </div>
        </div>
      </div>
    </Teleport>

    <!-- MODAL FORM JURUSAN -->
    <Teleport to="body">
      <div v-if="showMajorModal" class="modal-backdrop fade show"></div>
      <div v-if="showMajorModal" class="modal fade show d-block" tabindex="-1" @click.self="closeMajorModal">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            
            <div class="modal-header border-bottom px-4 py-3">
              <h6 class="modal-title fw-bold text-dark">{{ isEditingMajor ? 'Edit Jurusan' : 'Tambah Jurusan Baru' }}</h6>
              <button type="button" class="btn-close small shadow-none" @click="closeMajorModal" :disabled="majorStore.isSaving"></button>
            </div>

            <form @submit.prevent="submitMajor">
              <div class="modal-body p-4">
                
                <div class="mb-3">
                  <label for="major_name" class="form-label fw-medium text-dark small">Nama Jurusan <span class="text-danger">*</span></label>
                  <input 
                    type="text" 
                    id="major_name" 
                    class="form-control form-control-sm rounded-3 shadow-none"
                    :class="{ 'is-invalid': majorErrors.major_name }"
                    placeholder="Contoh: Rekayasa Perangkat Lunak"
                    v-model="majorForm.major_name"
                    required
                  >
                  <div v-if="majorErrors.major_name" class="invalid-feedback small">{{ majorErrors.major_name[0] }}</div>
                </div>

                <div class="mb-2">
                  <label for="major_status" class="form-label fw-medium text-dark small">Status <span class="text-danger">*</span></label>
                  <select 
                    id="major_status" 
                    class="form-select form-select-sm rounded-3 shadow-none"
                    :class="{ 'is-invalid': majorErrors.status }"
                    v-model="majorForm.status"
                    required
                  >
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                  </select>
                  <div v-if="majorErrors.status" class="invalid-feedback small">{{ majorErrors.status[0] }}</div>
                </div>

              </div>

              <div class="modal-footer border-top px-4 py-3 bg-light">
                <button 
                  type="button" 
                  class="btn btn-light rounded-3 py-2 px-3 small fw-medium border shadow-none"
                  @click="closeMajorModal"
                  :disabled="majorStore.isSaving"
                >
                  Batal
                </button>
                <button 
                  type="submit" 
                  class="btn btn-primary rounded-3 py-2 px-3 small fw-semibold shadow-none d-flex align-items-center gap-2"
                  :disabled="majorStore.isSaving"
                >
                  <span v-if="!majorStore.isSaving">Simpan</span>
                  <span v-else class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                </button>
              </div>
            </form>

          </div>
        </div>
      </div>
    </Teleport>

    <!-- MODAL DELETE (GENERIC CONFIRMATION) -->
    <Teleport to="body">
      <div v-if="showDeleteModal" class="modal-backdrop fade show"></div>
      <div v-if="showDeleteModal" class="modal fade show d-block" tabindex="-1" @click.self="closeDeleteModal">
        <div class="modal-dialog modal-dialog-centered modal-sm">
          <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="modal-body text-center p-4">
              
              <div class="delete-icon mx-auto mb-3 text-danger bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-exclamation-triangle fs-4"></i>
              </div>
              
              <h6 class="fw-bold text-dark mb-1">Hapus Data?</h6>
              <p class="text-secondary small mb-3">
                Apakah Anda yakin ingin menghapus <strong>{{ deleteTargetName }}</strong>?
              </p>

              <div v-if="deleteErrorMessage" class="alert alert-danger border-0 rounded-3 py-2 px-3 small text-start mb-3" role="alert">
                {{ deleteErrorMessage }}
              </div>

              <div class="d-flex gap-2">
                <button 
                  type="button" 
                  class="btn btn-light w-50 rounded-3 py-2 small fw-medium border shadow-none"
                  @click="closeDeleteModal"
                  :disabled="isDeleting"
                >
                  Batal
                </button>
                <button 
                  type="button" 
                  class="btn btn-danger w-50 rounded-3 py-2 small fw-semibold shadow-none d-flex align-items-center justify-content-center gap-2"
                  @click="confirmDelete"
                  :disabled="isDeleting"
                >
                  <span v-if="!isDeleting">Hapus</span>
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
import { useAcademicYearStore } from '../../stores/academicYear';
import { useMajorStore } from '../../stores/major';
import type { AcademicYear, AcademicYearPayload } from '../../types/academicYear';
import type { Major, MajorPayload } from '../../types/major';

const academicYearStore = useAcademicYearStore();
const majorStore = useMajorStore();

const activeTab = ref<'academic_years' | 'majors'>('academic_years');

// Computed messages
const currentSuccessMessage = computed(() => {
  return activeTab.value === 'academic_years' 
    ? academicYearStore.successMessage 
    : majorStore.successMessage;
});

const clearSuccessMessage = () => {
  academicYearStore.successMessage = null;
  majorStore.successMessage = null;
};

// --- ACADEMIC YEAR LOGIC ---
const showAcademicYearModal = ref(false);
const isEditingAY = ref(false);
const selectedAY = ref<AcademicYear | null>(null);

const ayForm = reactive<AcademicYearPayload>({
  name: '',
  semester: 'odd',
  start_date: '',
  end_date: '',
  is_active: false,
});

const ayErrors = reactive<Record<string, string[]>>({});

const resetAYForm = () => {
  ayForm.name = '';
  ayForm.semester = 'odd';
  ayForm.start_date = '';
  ayForm.end_date = '';
  ayForm.is_active = false;
  Object.keys(ayErrors).forEach(key => delete ayErrors[key]);
};

const openAcademicYearModal = (ay?: AcademicYear) => {
  resetAYForm();
  if (ay) {
    isEditingAY.value = true;
    selectedAY.value = ay;
    ayForm.name = ay.name;
    ayForm.semester = ay.semester;
    ayForm.start_date = ay.start_date ? ay.start_date.substring(0, 10) : '';
    ayForm.end_date = ay.end_date ? ay.end_date.substring(0, 10) : '';
    ayForm.is_active = Boolean(ay.is_active);
  } else {
    isEditingAY.value = false;
    selectedAY.value = null;
  }
  showAcademicYearModal.value = true;
};

const closeAcademicYearModal = () => {
  if (!academicYearStore.isSaving) {
    showAcademicYearModal.value = false;
  }
};

const submitAcademicYear = async () => {
  Object.keys(ayErrors).forEach(key => delete ayErrors[key]);

  let result;
  if (isEditingAY.value && selectedAY.value) {
    result = await academicYearStore.updateAcademicYear(selectedAY.value.id, ayForm);
  } else {
    result = await academicYearStore.createAcademicYear(ayForm);
  }

  if (result.success) {
    showAcademicYearModal.value = false;
  } else if (result.errors) {
    Object.assign(ayErrors, result.errors);
  }
};

const handleSetActive = async (ay: AcademicYear) => {
  await academicYearStore.setActiveAcademicYear(ay.id);
};

// --- MAJOR LOGIC ---
const showMajorModal = ref(false);
const isEditingMajor = ref(false);
const selectedMajor = ref<Major | null>(null);

const majorForm = reactive<MajorPayload>({
  major_name: '',
  status: 'active',
});

const majorErrors = reactive<Record<string, string[]>>({});

const resetMajorForm = () => {
  majorForm.major_name = '';
  majorForm.status = 'active';
  Object.keys(majorErrors).forEach(key => delete majorErrors[key]);
};

const openMajorModal = (major?: Major) => {
  resetMajorForm();
  if (major) {
    isEditingMajor.value = true;
    selectedMajor.value = major;
    majorForm.major_name = major.major_name;
    majorForm.status = major.status;
  } else {
    isEditingMajor.value = false;
    selectedMajor.value = null;
  }
  showMajorModal.value = true;
};

const closeMajorModal = () => {
  if (!majorStore.isSaving) {
    showMajorModal.value = false;
  }
};

const submitMajor = async () => {
  Object.keys(majorErrors).forEach(key => delete majorErrors[key]);

  let result;
  if (isEditingMajor.value && selectedMajor.value) {
    result = await majorStore.updateMajor(selectedMajor.value.id, majorForm);
  } else {
    result = await majorStore.createMajor(majorForm);
  }

  if (result.success) {
    showMajorModal.value = false;
  } else if (result.errors) {
    Object.assign(majorErrors, result.errors);
  }
};

// --- DELETE MODAL LOGIC ---
const showDeleteModal = ref(false);
const deleteType = ref<'academic_year' | 'major'>('academic_year');
const deleteTargetId = ref<number | null>(null);
const deleteTargetName = ref<string>('');
const deleteErrorMessage = ref<string | null>(null);
const isDeleting = ref(false);

const openDeleteAcademicYearModal = (ay: AcademicYear) => {
  deleteType.value = 'academic_year';
  deleteTargetId.value = ay.id;
  deleteTargetName.value = `Tahun Ajaran ${ay.name}`;
  deleteErrorMessage.value = null;
  showDeleteModal.value = true;
};

const openDeleteMajorModal = (major: Major) => {
  deleteType.value = 'major';
  deleteTargetId.value = major.id;
  deleteTargetName.value = `Jurusan ${major.major_name}`;
  deleteErrorMessage.value = null;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  if (!isDeleting.value) {
    showDeleteModal.value = false;
  }
};

const confirmDelete = async () => {
  if (!deleteTargetId.value) return;

  isDeleting.value = true;
  deleteErrorMessage.value = null;

  let result;
  if (deleteType.value === 'academic_year') {
    result = await academicYearStore.deleteAcademicYear(deleteTargetId.value);
  } else {
    result = await majorStore.deleteMajor(deleteTargetId.value);
  }

  isDeleting.value = false;

  if (result.success) {
    showDeleteModal.value = false;
  } else {
    deleteErrorMessage.value = result.message || 'Gagal menghapus data.';
  }
};

const formatDate = (dateStr: string) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  });
};

onMounted(() => {
  academicYearStore.fetchAcademicYears();
  majorStore.fetchMajors();
});
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
.bg-info-subtle {
  background-color: #f0f9ff !important;
  color: #0284c7 !important;
}
.bg-purple-subtle {
  background-color: #faf5ff !important;
  color: #9333ea !important;
}
.border-info-subtle {
  border-color: #bae6fd !important;
}
.border-purple-subtle {
  border-color: #e9d5ff !important;
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