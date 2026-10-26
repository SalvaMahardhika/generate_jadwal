<template>
  <SidebarAdmin>
    <div class="container-fluid px-0">
      
      <!-- Page Header -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4 pb-2 border-bottom">
        <div>
          <h5 class="fw-bold text-dark mb-1">Mata Pelajaran</h5>
          <p class="text-secondary small mb-0">Kelola master kode mapel, nama, KKM, warna label, indikator paralel, dan kategori mata pelajaran.</p>
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

      <!-- Filter Bar Component -->
      <SubjectFilterBar 
        :filters="subjectStore.filters"
        @update:search="handleSearchUpdate"
        @update:category="handleCategoryUpdate"
        @update:status="handleStatusUpdate"
        @update:is_joint_subject="handleJointSubjectUpdate"
        @reset="subjectStore.resetFilters()"
      />

      <!-- Table Component -->
      <SubjectTable 
        :subjects="subjectStore.subjects"
        :isLoading="subjectStore.isLoading"
        @edit="openEditModal"
        @delete="openDeleteModal"
      />

    </div>

    <!-- Modal Form (Tambah / Edit) -->
    <SubjectFormModal 
      :show="showFormModal"
      :isEditing="isEditing"
      :subject="selectedSubject"
      :isSaving="subjectStore.isSaving"
      :errors="formErrors"
      @close="showFormModal = false"
      @submit="submitForm"
    />

    <!-- Modal Delete -->
    <SubjectDeleteModal 
      :show="showDeleteModal"
      :subject="selectedSubject"
      :errorMessage="deleteErrorMessage"
      :isSaving="subjectStore.isSaving"
      @close="showDeleteModal = false"
      @confirm="confirmDelete"
    />

  </SidebarAdmin>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import SidebarAdmin from '../../layouts/SidebarAdmin.vue';
import SubjectFilterBar from './components/subject/SubjectFilterBar.vue';
import SubjectTable from './components/subject/SubjectTable.vue';
import SubjectFormModal from './components/subject/SubjectFormModal.vue';
import SubjectDeleteModal from './components/subject/SubjectDeleteModal.vue';

import { useSubjectStore } from '../../stores/subject';
import type { Subject, SubjectPayload } from '../../types/subject';

const subjectStore = useSubjectStore();

const showFormModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const selectedSubject = ref<Subject | null>(null);
const deleteErrorMessage = ref<string | null>(null);

const formErrors = reactive<Record<string, string[]>>({});

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

onMounted(() => {
  subjectStore.fetchSubjects();
});

const handleSearchUpdate = (val: string) => {
  subjectStore.filters.search = val;
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    subjectStore.fetchSubjects();
  }, 300);
};

const handleCategoryUpdate = (val: string) => {
  subjectStore.filters.category = val;
  subjectStore.fetchSubjects();
};

const handleStatusUpdate = (val: string) => {
  subjectStore.filters.status = val;
  subjectStore.fetchSubjects();
};

const handleJointSubjectUpdate = (val: string) => {
  subjectStore.filters.is_joint_subject = val;
  subjectStore.fetchSubjects();
};

const openCreateModal = () => {
  Object.keys(formErrors).forEach(key => delete formErrors[key]);
  isEditing.value = false;
  selectedSubject.value = null;
  showFormModal.value = true;
};

const openEditModal = (subject: Subject) => {
  Object.keys(formErrors).forEach(key => delete formErrors[key]);
  isEditing.value = true;
  selectedSubject.value = subject;
  showFormModal.value = true;
};

const submitForm = async (payload: SubjectPayload) => {
  Object.keys(formErrors).forEach(key => delete formErrors[key]);

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