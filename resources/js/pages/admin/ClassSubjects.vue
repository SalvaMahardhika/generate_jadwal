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

      <!-- Selector Pilih Kelas & Summary Card Component -->
      <ClassSubjectFilterCard 
        v-model="selectedClassId"
        :classesList="classesList"
        :currentClass="plottingStore.currentClass"
        :totalJpAllocated="totalJpAllocated"
        :totalSubjects="plottingStore.classSubjects.length"
        @update:modelValue="onClassChange"
      />

      <!-- Table Plotting Class Subjects Component -->
      <ClassSubjectTable 
        :selectedClassId="selectedClassId"
        :isLoading="plottingStore.isLoading"
        :classSubjects="plottingStore.classSubjects"
        @edit="openEditModal"
        @delete="openDeleteModal"
      />

    </div>

    <!-- Modal Form Component (Assign / Edit) -->
    <ClassSubjectFormModal 
      :show="showFormModal"
      :isEditing="isEditing"
      :isSaving="plottingStore.isSaving"
      :selectedCS="selectedCS"
      :unassignedSubjects="unassignedSubjects"
      :teachersList="teachersList"
      :form="form"
      :errors="formErrors"
      @close="closeFormModal"
      @submit="submitForm"
    />

    <!-- Modal Delete Component -->
    <ClassSubjectDeleteModal 
      :show="showDeleteModal"
      :isSaving="plottingStore.isSaving"
      :selectedCS="selectedCS"
      :errorMessage="deleteErrorMessage"
      @close="closeDeleteModal"
      @confirm="confirmDelete"
    />

  </SidebarAdmin>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import SidebarAdmin from '../../layouts/SidebarAdmin.vue';
import ClassSubjectFilterCard from './components/classsubject/ClassSubjectFilterCard.vue';
import ClassSubjectTable from './components/classsubject/ClassSubjectTable.vue';
import ClassSubjectFormModal from './components/classsubject/ClassSubjectFormModal.vue';
import ClassSubjectDeleteModal from './components/classsubject/ClassSubjectDeleteModal.vue';
import { useClassSubjectStore } from '../../stores/classSubject';
import api from '../../services/api';
import type { ClassItem, TeacherOption } from '../../types/class';
import type { Subject } from '../../types/subject';
import type { ClassSubject, TeacherAssignmentInput, AssignClassSubjectPayload, UpdateClassSubjectPayload } from '../../types/classSubject';

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
  teachers: [] as TeacherAssignmentInput[],
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
  form.teachers = [];
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
  form.teachers = cs.class_subject_teachers
    ? cs.class_subject_teachers.map(t => ({ teacher_id: t.teacher_id, jp: t.jp || 0 }))
    : [];

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
    const payload: UpdateClassSubjectPayload = {
      total_jp: Number(form.total_jp),
      max_jp_per_day: Number(form.max_jp_per_day),
      teachers: form.teachers.map(t => ({ teacher_id: t.teacher_id, jp: Number(t.jp) })),
    };
    result = await plottingStore.updateAssignment(selectedCS.value.id, Number(selectedClassId.value), payload);
  } else {
    const payload: AssignClassSubjectPayload = {
      subject_id: Number(form.subject_id),
      total_jp: Number(form.total_jp),
      max_jp_per_day: Number(form.max_jp_per_day),
      teachers: form.teachers.map(t => ({ teacher_id: t.teacher_id, jp: Number(t.jp) })),
    };
    result = await plottingStore.assignSubject(Number(selectedClassId.value), payload);
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