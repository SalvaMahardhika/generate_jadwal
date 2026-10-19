<template>
  <SidebarAdmin>
    <div class="container-fluid px-0">
      
      <!-- Page Header -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4 pb-2 border-bottom">
        <div>
          <h5 class="fw-bold text-dark mb-1">Plotting Mapel & Guru</h5>
          <p class="text-secondary small mb-0">Atur alokasi jam pelajaran (JP) dan tetapkan guru pengampu mata pelajaran per kelas.</p>
        </div>
        <button 
          type="button" 
          class="btn btn-primary rounded-3 py-2 px-3 small fw-medium d-flex align-items-center gap-2"
          :disabled="!selectedClassId"
          @click="openAssignModal()"
        >
          <i class="bi bi-plus-lg"></i>
          <span>Tambah Plotting Mapel</span>
        </button>
      </div>

      <!-- Alert Pesan Sukses -->
      <div v-if="plottingStore.successMessage" class="alert alert-success border-0 rounded-3 py-2 px-3 small d-flex align-items-center justify-content-between mb-3" role="alert">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill"></i>
          <span>{{ plottingStore.successMessage }}</span>
        </div>
        <button type="button" class="btn-close small" @click="plottingStore.successMessage = null"></button>
      </div>

      <!-- Selector Pilih Kelas & Summary Card -->
      <div class="card border rounded-3 p-3 bg-white mb-4">
        <div class="row g-3 align-items-center">
          <div class="col-12 col-md-5">
            <label for="class_select" class="form-label fw-semibold text-dark small mb-1">Pilih Kelas yang Dikelola:</label>
            <select 
              id="class_select" 
              class="form-select form-select-sm bg-light shadow-none" 
              v-model="selectedClassId"
              @change="onClassChange"
            >
              <option value="">-- Pilih Kelas --</option>
              <option v-for="cls in classesList" :key="cls.id" :value="cls.id">
                {{ cls.class_name }} - {{ cls.grade?.grade_name || '' }} ({{ cls.academic_year?.name || '' }})
              </option>
            </select>
          </div>

          <div class="col-12 col-md-7" v-if="plottingStore.currentClass">
            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
              <div class="p-2 px-3 bg-light rounded-3 border">
                <small class="text-muted d-block fs-8">Total Terplot</small>
                <span class="fw-bold text-primary small">{{ totalJpAllocated }} JP / Minggu</span>
              </div>
              <div class="p-2 px-3 bg-light rounded-3 border">
                <small class="text-muted d-block fs-8">Jumlah Mapel</small>
                <span class="fw-bold text-dark small">{{ plottingStore.classSubjects.length }} Mapel</span>
              </div>
              <div class="p-2 px-3 bg-light rounded-3 border">
                <small class="text-muted d-block fs-8">Wali Kelas</small>
                <span class="fw-bold text-dark small">{{ plottingStore.currentClass.homeroom_teacher?.name || '-' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Table Plotting Class Subjects -->
      <div class="card border rounded-3 bg-white overflow-hidden shadow-sm">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="bg-light border-bottom">
              <tr>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small" style="width: 50px;">#</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Mata Pelajaran</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Beban JP/Minggu</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Maks JP/Hari</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Guru Pengampu</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small text-end" style="width: 120px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!selectedClassId">
                <td colspan="6" class="text-center py-5 text-muted small">
                  <i class="bi bi-arrow-up-circle fs-3 d-block mb-2 text-primary opacity-50"></i>
                  Silakan pilih kelas terlebih dahulu untuk menampilkan data plotting.
                </td>
              </tr>

              <tr v-else-if="plottingStore.isLoading">
                <td colspan="6" class="text-center py-5 text-muted small">
                  <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                  Memuat data plotting mata pelajaran...
                </td>
              </tr>

              <tr v-else-if="plottingStore.classSubjects.length === 0">
                <td colspan="6" class="text-center py-5 text-muted small">
                  <i class="bi bi-journal-x fs-3 d-block mb-2 text-secondary opacity-50"></i>
                  Belum ada mata pelajaran yang dialokasikan di kelas ini.
                </td>
              </tr>

              <tr v-else v-for="(cs, index) in plottingStore.classSubjects" :key="cs.id">
                <td class="px-3 small text-secondary">{{ index + 1 }}</td>
                <td class="px-3">
                  <div class="d-flex align-items-center gap-2">
                    <span 
                      class="color-dot rounded-circle flex-shrink-0" 
                      :style="{ backgroundColor: cs.subject?.color_code || '#cbd5e1' }"
                    ></span>
                    <div>
                      <div class="fw-bold text-dark small mb-0">{{ cs.subject?.subject_name }}</div>
                      <small class="text-muted fs-8 font-monospace">{{ cs.subject?.subject_code }}</small>
                    </div>
                  </div>
                </td>
                <td class="px-3 small">
                  <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fs-8 fw-bold">
                    {{ cs.total_jp }} JP
                  </span>
                </td>
                <td class="px-3 small text-secondary">
                  {{ cs.max_jp_per_day }} JP / Hari
                </td>
                <td class="px-3 small">
                  <div class="d-flex flex-wrap gap-1" v-if="cs.class_subject_teachers && cs.class_subject_teachers.length > 0">
                    <span 
                      v-for="cst in cs.class_subject_teachers" 
                      :key="cst.id"
                      class="badge bg-light text-dark border px-2 py-1 fs-8 fw-normal"
                    >
                      <i class="bi bi-person-fill text-secondary me-1"></i>
                      {{ cst.teacher?.name }}
                    </span>
                  </div>
                  <span v-else class="text-muted fst-italic fs-8">Belum ada guru</span>
                </td>
                <td class="px-3 text-end">
                  <div class="d-flex justify-content-end gap-1">
                    <button 
                      type="button" 
                      class="btn btn-light btn-sm text-secondary border-0 rounded-2 px-2 py-1"
                      title="Edit Plotting"
                      @click="openEditModal(cs)"
                    >
                      <i class="bi bi-pencil-square fs-7"></i>
                    </button>
                    <button 
                      type="button" 
                      class="btn btn-light btn-sm text-danger border-0 rounded-2 px-2 py-1"
                      title="Hapus Plotting"
                      @click="openDeleteModal(cs)"
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

    <!-- Modal Form (Assign / Edit) -->
    <Teleport to="body">
      <div v-if="showFormModal" class="modal-backdrop fade show"></div>
      <div v-if="showFormModal" class="modal fade show d-block" tabindex="-1" @click.self="closeFormModal">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            
            <div class="modal-header border-bottom px-4 py-3">
              <h6 class="modal-title fw-bold text-dark">{{ isEditing ? 'Edit Plotting Mapel' : 'Assign Mapel & Guru ke Kelas' }}</h6>
              <button type="button" class="btn-close small shadow-none" @click="closeFormModal" :disabled="plottingStore.isSaving"></button>
            </div>

            <form @submit.prevent="submitForm">
              <div class="modal-body p-4">
                
                <!-- Pilih Mata Pelajaran (Hanya saat Tambah Baru) -->
                <div class="mb-3" v-if="!isEditing">
                  <label for="subject_id" class="form-label fw-medium text-dark small">Mata Pelajaran <span class="text-danger">*</span></label>
                  <select 
                    id="subject_id" 
                    class="form-select form-select-sm rounded-3 shadow-none"
                    :class="{ 'is-invalid': formErrors.subject_id }"
                    v-model="form.subject_id"
                    required
                  >
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    <option v-for="subject in unassignedSubjects" :key="subject.id" :value="subject.id">
                      {{ subject.subject_code }} - {{ subject.subject_name }}
                    </option>
                  </select>
                  <div v-if="formErrors.subject_id" class="invalid-feedback small">{{ formErrors.subject_id[0] }}</div>
                </div>

                <div class="mb-3" v-else>
                  <label class="form-label fw-medium text-dark small">Mata Pelajaran</label>
                  <input 
                    type="text" 
                    class="form-control form-control-sm rounded-3 bg-light" 
                    :value="selectedCS?.subject?.subject_name" 
                    disabled
                  >
                </div>

                <!-- Total JP & Maks JP Per Hari -->
                <div class="row g-3 mb-3">
                  <div class="col-12 col-sm-6">
                    <label for="total_jp" class="form-label fw-medium text-dark small">Total JP / Minggu <span class="text-danger">*</span></label>
                    <input 
                      type="number" 
                      id="total_jp" 
                      class="form-control form-control-sm rounded-3 shadow-none"
                      :class="{ 'is-invalid': formErrors.total_jp }"
                      placeholder="Contoh: 4"
                      v-model="form.total_jp"
                      min="1"
                      required
                    >
                    <div v-if="formErrors.total_jp" class="invalid-feedback small">{{ formErrors.total_jp[0] }}</div>
                  </div>

                  <div class="col-12 col-sm-6">
                    <label for="max_jp_per_day" class="form-label fw-medium text-dark small">Maks JP / Hari <span class="text-danger">*</span></label>
                    <input 
                      type="number" 
                      id="max_jp_per_day" 
                      class="form-control form-control-sm rounded-3 shadow-none"
                      :class="{ 'is-invalid': formErrors.max_jp_per_day }"
                      placeholder="Contoh: 2"
                      v-model="form.max_jp_per_day"
                      min="1"
                      required
                    >
                    <div v-if="formErrors.max_jp_per_day" class="invalid-feedback small">{{ formErrors.max_jp_per_day[0] }}</div>
                  </div>
                </div>

                <!-- Multi Checkbox Guru Pengampu -->
                <div class="mb-2">
                  <label class="form-label fw-medium text-dark small mb-1">Guru Pengampu <span class="text-danger">*</span></label>
                  <div 
                    class="teacher-select-box border rounded-3 p-3 bg-light overflow-y-auto"
                    :class="{ 'border-danger': formErrors.teacher_ids }"
                    style="max-height: 180px;"
                  >
                    <div v-if="teachersList.length === 0" class="text-muted small">Data guru tidak ditemukan.</div>
                    <div 
                      v-else 
                      v-for="teacher in teachersList" 
                      :key="teacher.id"
                      class="form-check mb-1.5"
                    >
                      <input 
                        class="form-check-input shadow-none" 
                        type="checkbox" 
                        :id="`teacher_${teacher.id}`"
                        :value="teacher.id"
                        v-model="form.teacher_ids"
                      >
                      <label class="form-check-label small text-dark" :for="`teacher_${teacher.id}`">
                        {{ teacher.name }} <span class="text-muted fs-8" v-if="teacher.nip">({{ teacher.nip }})</span>
                      </label>
                    </div>
                  </div>
                  <div v-if="formErrors.teacher_ids" class="text-danger small mt-1">{{ formErrors.teacher_ids[0] }}</div>
                </div>

              </div>

              <div class="modal-footer border-top px-4 py-3 bg-light">
                <button 
                  type="button" 
                  class="btn btn-light rounded-3 py-2 px-3 small fw-medium border shadow-none"
                  @click="closeFormModal"
                  :disabled="plottingStore.isSaving"
                >
                  Batal
                </button>
                <button 
                  type="submit" 
                  class="btn btn-primary rounded-3 py-2 px-3 small fw-semibold shadow-none d-flex align-items-center gap-2"
                  :disabled="plottingStore.isSaving"
                >
                  <span v-if="!plottingStore.isSaving">Simpan</span>
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
              
              <h6 class="fw-bold text-dark mb-1">Hapus Pemetaan?</h6>
              <p class="text-secondary small mb-3">
                Apakah Anda yakin ingin menghapus <strong>{{ selectedCS?.subject?.subject_name }}</strong> dari kelas ini?
              </p>

              <div v-if="deleteErrorMessage" class="alert alert-danger border-0 rounded-3 py-2 px-3 small text-start mb-3" role="alert">
                {{ deleteErrorMessage }}
              </div>

              <div class="d-flex gap-2">
                <button 
                  type="button" 
                  class="btn btn-light w-50 rounded-3 py-2 small fw-medium border shadow-none"
                  @click="closeDeleteModal"
                  :disabled="plottingStore.isSaving"
                >
                  Batal
                </button>
                <button 
                  type="button" 
                  class="btn btn-danger w-50 rounded-3 py-2 small fw-semibold shadow-none d-flex align-items-center justify-content-center gap-2"
                  @click="confirmDelete"
                  :disabled="plottingStore.isSaving"
                >
                  <span v-if="!plottingStore.isSaving">Hapus</span>
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
import { useRoute, useRouter } from 'vue-router';
import SidebarAdmin from '../../layouts/SidebarAdmin.vue';
import { useClassSubjectStore } from '../../stores/classSubject';
import api from '../../services/api';
import type { ClassItem, TeacherOption } from '../../types/class';
import type { Subject } from '../../types/subject';
import type { ClassSubject } from '../../types/classSubject';

const route = useRoute();
const router = useRouter();
const plottingStore = useClassSubjectStore();

const selectedClassId = ref<number | string>('');
const classesList = ref<ClassItem[]>([]);
const subjectsList = ref<Subject[]>([]);
const teachersList = ref<TeacherOption[]>([]);

const showFormModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const selectedCS = ref<ClassSubject | null>(null);
const deleteErrorMessage = ref<string | null>(null);

const form = reactive({
  subject_id: '' as number | string,
  total_jp: '' as number | string,
  max_jp_per_day: 2 as number | string,
  teacher_ids: [] as number[],
});

const formErrors = reactive<Record<string, string[]>>({});

const totalJpAllocated = computed(() => {
  return plottingStore.classSubjects.reduce((sum, item) => sum + (Number(item.total_jp) || 0), 0);
});

// Daftar Mapel yang belum dimasukkan ke kelas saat ini
const unassignedSubjects = computed(() => {
  const assignedIds = plottingStore.classSubjects.map(cs => cs.subject_id);
  return subjectsList.value.filter(s => !assignedIds.includes(s.id));
});

const fetchDropdownData = async () => {
  try {
    const [classesRes, subjectsRes, teachersRes] = await Promise.all([
      api.get('/classes'),
      api.get('/subjects'),
      api.get('/teachers'),
    ]);

    if (classesRes.data?.status === 'success') classesList.value = classesRes.data.data;
    if (subjectsRes.data?.status === 'success') subjectsList.value = subjectsRes.data.data;
    if (teachersRes.data?.status === 'success') teachersList.value = teachersRes.data.data;
  } catch (err) {
    console.error('Gagal memuat data opsi dropdown:', err);
  }
};

const onClassChange = () => {
  if (selectedClassId.value) {
    router.replace({ query: { class_id: selectedClassId.value } });
    plottingStore.fetchClassSubjects(Number(selectedClassId.value));
  } else {
    router.replace({ query: {} });
    plottingStore.classSubjects = [];
    plottingStore.currentClass = null;
  }
};

onMounted(async () => {
  await fetchDropdownData();

  // Membaca parameter query `class_id` dari URL jika diklik dari Halaman Kelas
  const queryClassId = route.query.class_id;
  if (queryClassId) {
    selectedClassId.value = Number(queryClassId);
    plottingStore.fetchClassSubjects(Number(queryClassId));
  } else if (classesList.value.length > 0) {
    selectedClassId.value = classesList.value[0].id;
    onClassChange();
  }
});

const resetForm = () => {
  form.subject_id = '';
  form.total_jp = '';
  form.max_jp_per_day = 2;
  form.teacher_ids = [];
  Object.keys(formErrors).forEach(key => delete formErrors[key]);
};

const openAssignModal = () => {
  resetForm();
  isEditing.value = false;
  selectedCS.value = null;
  showFormModal.value = true;
};

const openEditModal = (cs: ClassSubject) => {
  resetForm();
  isEditing.value = true;
  selectedCS.value = cs;

  form.subject_id = cs.subject_id;
  form.total_jp = cs.total_jp;
  form.max_jp_per_day = cs.max_jp_per_day;
  form.teacher_ids = cs.class_subject_teachers ? cs.class_subject_teachers.map(t => t.teacher_id) : [];

  showFormModal.value = true;
};

const closeFormModal = () => {
  if (!plottingStore.isSaving) {
    showFormModal.value = false;
  }
};

const submitForm = async () => {
  if (!selectedClassId.value) return;

  Object.keys(formErrors).forEach(key => delete formErrors[key]);

  let result;
  if (isEditing.value && selectedCS.value) {
    result = await plottingStore.updateAssignment(
      selectedCS.value.id,
      Number(selectedClassId.value),
      {
        total_jp: Number(form.total_jp),
        max_jp_per_day: Number(form.max_jp_per_day),
        teacher_ids: form.teacher_ids,
      }
    );
  } else {
    result = await plottingStore.assignSubject(
      Number(selectedClassId.value),
      {
        subject_id: Number(form.subject_id),
        total_jp: Number(form.total_jp),
        max_jp_per_day: Number(form.max_jp_per_day),
        teacher_ids: form.teacher_ids,
      }
    );
  }

  if (result.success) {
    showFormModal.value = false;
  } else if (result.errors) {
    Object.assign(formErrors, result.errors);
  }
};

const openDeleteModal = (cs: ClassSubject) => {
  selectedCS.value = cs;
  deleteErrorMessage.value = null;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  if (!plottingStore.isSaving) {
    showDeleteModal.value = false;
  }
};

const confirmDelete = async () => {
  if (!selectedCS.value || !selectedClassId.value) return;

  deleteErrorMessage.value = null;
  const result = await plottingStore.deleteAssignment(selectedCS.value.id, Number(selectedClassId.value));

  if (result.success) {
    showDeleteModal.value = false;
  } else {
    deleteErrorMessage.value = result.message || 'Gagal menghapus pemetaan mapel.';
  }
};
</script>

<style scoped>
.color-dot {
  width: 12px;
  height: 12px;
}
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
.border-primary-subtle {
  border-color: #bfdbfe !important;
}
.fs-7 {
  font-size: 0.82rem;
}
.fs-8 {
  font-size: 0.72rem;
}
</style>