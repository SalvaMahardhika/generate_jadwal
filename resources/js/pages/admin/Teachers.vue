<template>
  <SidebarAdmin>
    <div class="container-fluid px-0">
      
      <!-- Page Header -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4 pb-2 border-bottom">
        <div>
          <h5 class="fw-bold text-dark mb-1">Data Guru</h5>
          <p class="text-secondary small mb-0">Kelola informasi pengajar, NIP, beban JP per minggu, dan status keaktifan.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
          <!-- Dropdown Aksi Excel -->
          <div class="dropdown">
            <button
              class="btn btn-outline-success rounded-3 py-2 px-3 small fw-medium dropdown-toggle d-flex align-items-center gap-2 shadow-none"
              type="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <i class="bi bi-file-earmark-excel"></i>
              <span>Excel</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 text-start fs-8">
              <li>
                <button class="dropdown-item py-2 px-3 d-flex align-items-center gap-2" type="button" @click="handleDownloadTemplate">
                  <i class="bi bi-download text-muted"></i>
                  <span>Download Template Import</span>
                </button>
              </li>
              <li>
                <button class="dropdown-item py-2 px-3 d-flex align-items-center gap-2" type="button" @click="handleExportData">
                  <i class="bi bi-file-earmark-arrow-down text-muted"></i>
                  <span>Export Data Guru</span>
                </button>
              </li>
              <li><hr class="dropdown-divider my-1"></li>
              <li>
                <button class="dropdown-item py-2 px-3 d-flex align-items-center gap-2 text-success fw-medium" type="button" @click="openImportModal">
                  <i class="bi bi-file-earmark-arrow-up"></i>
                  <span>Impor dari Excel</span>
                </button>
              </li>
            </ul>
          </div>

          <!-- Tombol Tambah Guru Manual -->
          <button 
            type="button" 
            class="btn btn-primary rounded-3 py-2 px-3 small fw-medium d-flex align-items-center gap-2"
            @click="openCreateModal"
          >
            <i class="bi bi-plus-lg"></i>
            <span>Tambah Guru</span>
          </button>
        </div>
      </div>

      <!-- Alert Pesan Sukses / Error Global -->
      <div v-if="teacherStore.successMessage" class="alert alert-success border-0 rounded-3 py-2 px-3 small d-flex align-items-center justify-content-between mb-3" role="alert">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill"></i>
          <span>{{ teacherStore.successMessage }}</span>
        </div>
        <button type="button" class="btn-close small" @click="teacherStore.successMessage = null"></button>
      </div>

      <!-- Filter & Search Bar Component -->
      <TeacherFilterBar 
        :filters="teacherStore.filters"
        @update:search="handleSearchUpdate"
        @update:status="handleStatusUpdate"
        @reset="teacherStore.resetFilters()"
      />

      <!-- Table Component -->
      <TeacherTable 
        :teachers="teacherStore.teachers"
        :isLoading="teacherStore.isLoading"
        @edit="openEditModal"
        @delete="openDeleteModal"
      />

    </div>

    <!-- Modal Form (Tambah / Edit Guru) -->
    <TeacherFormModal 
      :show="showFormModal"
      :isEditing="isEditing"
      :teacher="selectedTeacher"
      :isSaving="teacherStore.isSaving"
      :errors="formErrors"
      @close="showFormModal = false"
      @submit="submitForm"
      @openImport="openImportModal"
    />

    <!-- Modal Import Excel -->
    <TeacherImportModal 
      :show="showImportModal"
      :isSaving="teacherStore.isSaving"
      :existingTeachers="teacherStore.teachers"
      @close="showImportModal = false"
      @importSuccess="handleImportSuccess"
    />

    <!-- Modal Konfirmasi Hapus -->
    <TeacherDeleteModal 
      :show="showDeleteModal"
      :teacher="selectedTeacher"
      :errorMessage="deleteErrorMessage"
      :isSaving="teacherStore.isSaving"
      @close="showDeleteModal = false"
      @confirm="confirmDelete"
    />

  </SidebarAdmin>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import SidebarAdmin from '../../layouts/SidebarAdmin.vue';
import TeacherFilterBar from './components/teacher/TeacherFilterBar.vue';
import TeacherTable from './components/teacher/TeacherTable.vue';
import TeacherFormModal from './components/teacher/TeacherFormModal.vue';
import TeacherDeleteModal from './components/teacher/TeacherDeleteModal.vue';
import TeacherImportModal from './components/teacher/TeacherImportModal.vue';

import { useTeacherStore } from '../../stores/teacher';
import { exportTeacherExcel } from '../../utils/teacherExcelExporter';
import type { Teacher, TeacherPayload } from '../../types/teacher';

const teacherStore = useTeacherStore();

const showFormModal = ref(false);
const showDeleteModal = ref(false);
const showImportModal = ref(false);

const isEditing = ref(false);
const selectedTeacher = ref<Teacher | null>(null);
const deleteErrorMessage = ref<string | null>(null);

const formErrors = reactive<Record<string, string[]>>({});

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

onMounted(() => {
  teacherStore.fetchTeachers();
});

const handleSearchUpdate = (val: string) => {
  teacherStore.filters.search = val;
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    teacherStore.fetchTeachers();
  }, 300);
};

const handleStatusUpdate = (val: string) => {
  teacherStore.filters.status = val;
  teacherStore.fetchTeachers();
};

// Excel Aksi
const handleDownloadTemplate = () => {
  exportTeacherExcel([], true);
};

const handleExportData = () => {
  exportTeacherExcel(teacherStore.teachers, false);
};

const openImportModal = () => {
  showFormModal.value = false;
  showImportModal.value = true;
};

const handleImportSuccess = async (payloads: TeacherPayload[]) => {
  const result = await teacherStore.importTeachers(payloads);
  if (result.success) {
    showImportModal.value = false;
  }
};

const openCreateModal = () => {
  Object.keys(formErrors).forEach(key => delete formErrors[key]);
  isEditing.value = false;
  selectedTeacher.value = null;
  showFormModal.value = true;
};

const openEditModal = (teacher: Teacher) => {
  Object.keys(formErrors).forEach(key => delete formErrors[key]);
  isEditing.value = true;
  selectedTeacher.value = teacher;
  showFormModal.value = true;
};

const submitForm = async (payload: TeacherPayload) => {
  Object.keys(formErrors).forEach(key => delete formErrors[key]);

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
.fs-8 {
  font-size: 0.75rem;
}
</style>