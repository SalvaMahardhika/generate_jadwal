<template>
  <SidebarAdmin>
    <div class="container-fluid px-0">
      
      <!-- PAGE TITLE -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4 pb-2 border-bottom">
        <div>
          <h5 class="fw-bold text-dark mb-1">Wizard Auto-Generate & Editor Jadwal</h5>
          <p class="text-secondary small mb-0">Kelola penyusunan jadwal pelajaran otomatis.</p>
        </div>
      </div>

      <!-- ALERTS -->
      <div v-if="wizardStore.successMessage" class="alert alert-success border-0 rounded-3 py-2 px-3 small d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill"></i>
          <span>{{ wizardStore.successMessage }}</span>
        </div>
        <button type="button" class="btn-close small" @click="wizardStore.successMessage = null"></button>
      </div>

      <div v-if="wizardStore.errorMessage" class="alert alert-danger border-0 rounded-3 py-2 px-3 small d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <span>{{ wizardStore.errorMessage }}</span>
        </div>
        <button type="button" class="btn-close small" @click="wizardStore.errorMessage = null"></button>
      </div>

      <!-- HEADER CONTROLLER -->
      <WizardHeader 
        v-model:viewMode="wizardStore.viewMode"
        v-model:selectedAcademicYearId="wizardStore.selectedAcademicYearId"
        v-model:selectedClassId="wizardStore.selectedClassId"
        v-model:selectedTeacherId="wizardStore.selectedTeacherId"
        :academicYears="academicYearsList"
        :classes="classesList"
        :teachers="teachersList"
        :isProcessing="wizardStore.isProcessing"
        @generate="handleGenerate"
        @reset="handleReset"
        @publish="handlePublish"
      />

      <!-- WORKSPACE VIEWS -->
      <div class="row g-3" v-if="wizardStore.viewMode === 'class'">
        <div class="col-12 col-xl-9">
          <ClassMatrixView 
            :timeSlots="wizardStore.timeSlots"
            :schedules="wizardStore.classMatrix?.schedules || {}"
            :isLoading="wizardStore.isLoading"
            @cellClick="openAssignModal"
          />
        </div>
        <div class="col-12 col-xl-3">
          <JpBalanceSidebar 
            :balances="wizardStore.classMatrix?.balance || []"
          />
        </div>
      </div>

      <div v-else-if="wizardStore.viewMode === 'teacher'">
        <TeacherMatrixView 
          :teacherName="wizardStore.teacherMatrix?.teacher?.name"
          :totalAssignedJp="wizardStore.teacherMatrix?.total_assigned_jp || 0"
          :timeSlots="wizardStore.timeSlots"
          :schedules="wizardStore.teacherMatrix?.schedules || {}"
          :isLoading="wizardStore.isLoading"
        />
      </div>

      <div v-else-if="wizardStore.viewMode === 'master'">
        <AllClassesMatrixView 
          :classes="classesList"
          :timeSlots="wizardStore.timeSlots"
          :matrix="wizardStore.masterMatrix?.matrix || {}"
          :isLoading="wizardStore.isLoading"
          @cellClick="openAssignModal"
        />
      </div>

    </div>

    <!-- MODAL EDIT SLOT -->
    <AssignSlotModal 
      :show="showModal"
      :timeSlot="activeSlot"
      :currentSchedule="activeSchedule"
      :availableSubjects="availableClassSubjects"
      :isProcessing="wizardStore.isProcessing"
      @close="showModal = false"
      @save="handleSaveSlot"
      @clear="handleClearSlot"
    />
  </SidebarAdmin>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue';
import SidebarAdmin from '../../layouts/SidebarAdmin.vue';
import WizardHeader from './components/wizard/WizardHeader.vue';
import JpBalanceSidebar from './components/wizard/JpBalanceSidebar.vue';
import ClassMatrixView from './components/wizard/ClassMatrixView.vue';
import TeacherMatrixView from './components/wizard/TeacherMatrixView.vue';
import AllClassesMatrixView from './components/wizard/AllClassesMatrixView.vue';
import AssignSlotModal from './components/wizard/AssignSlotModal.vue';

import { useWizardStore } from '../../stores/wizard';
import api from '../../services/api';
import type { AcademicYear } from '../../types/academicYear';
import type { ClassItem, TeacherOption } from '../../types/class';
import type { TimeSlot, ScheduleItem, ClassSubjectTeacherDetail } from '../../types/wizard';

const wizardStore = useWizardStore();

const academicYearsList = ref<AcademicYear[]>([]);
const classesList = ref<ClassItem[]>([]);
const teachersList = ref<TeacherOption[]>([]);
const classSubjectsMap = ref<ClassSubjectTeacherDetail[]>([]);

const showModal = ref(false);
const activeSlot = ref<TimeSlot | null>(null);
const activeSchedule = ref<ScheduleItem | null>(null);
const activeClassId = ref<number | null>(null);

const isInitializing = ref(true);

const availableClassSubjects = computed(() => classSubjectsMap.value);

const fetchDropdowns = async () => {
  try {
    const [ayRes, clsRes, tchrRes] = await Promise.all([
      api.get('/academic-years'),
      api.get('/classes'),
      api.get('/teachers'),
    ]);

    if (ayRes.data?.status === 'success') {
      academicYearsList.value = ayRes.data.data || [];
      if (!wizardStore.selectedAcademicYearId) {
        const activeAy = academicYearsList.value.find(a => a.is_active);
        if (activeAy) {
          wizardStore.selectedAcademicYearId = activeAy.id;
        } else if (academicYearsList.value.length > 0) {
          wizardStore.selectedAcademicYearId = academicYearsList.value[0].id;
        }
      }
    }

    if (clsRes.data?.status === 'success') {
      classesList.value = clsRes.data.data || [];
      if (classesList.value.length > 0 && !wizardStore.selectedClassId) {
        wizardStore.selectedClassId = classesList.value[0].id;
      }
    }

    if (tchrRes.data?.status === 'success') {
      teachersList.value = tchrRes.data.data || [];
      if (teachersList.value.length > 0 && !wizardStore.selectedTeacherId) {
        wizardStore.selectedTeacherId = teachersList.value[0].id;
      }
    }
  } catch (err) {
    console.error('Gagal memuat opsi dropdown:', err);
  }
};

const fetchClassSubjects = async (classId: number) => {
  try {
    const res = await api.get(`/classes/${classId}/subjects`);
    if (res.data?.status === 'success') {
      const rawSubjects = res.data.data.subjects || [];
      const list: ClassSubjectTeacherDetail[] = [];
      rawSubjects.forEach((cs: any) => {
        if (cs.class_subject_teachers && cs.class_subject_teachers.length > 0) {
          cs.class_subject_teachers.forEach((cst: any) => {
            list.push({
              id: cst.id,
              class_subject_id: cs.id,
              teacher_id: cst.teacher_id,
              jp: cst.jp,
              class_subject: cs,
              teacher: cst.teacher
            });
          });
        }
      });
      classSubjectsMap.value = list;
    }
  } catch (err) {
    console.error('Gagal memuat mapel kelas:', err);
  }
};

watch(() => wizardStore.viewMode, () => {
  if (isInitializing.value) return;
  wizardStore.refreshCurrentView();
});

watch(() => wizardStore.selectedAcademicYearId, () => {
  if (isInitializing.value) return;
  wizardStore.refreshCurrentView();
});

watch(() => wizardStore.selectedClassId, async (newId) => {
  if (isInitializing.value || !newId) return;
  await fetchClassSubjects(newId);
  if (wizardStore.viewMode === 'class') {
    wizardStore.fetchClassMatrix(newId);
  }
});

watch(() => wizardStore.selectedTeacherId, (newId) => {
  if (isInitializing.value || !newId) return;
  if (wizardStore.viewMode === 'teacher') {
    wizardStore.fetchTeacherMatrix(newId);
  }
});

onMounted(async () => {
  isInitializing.value = true;
  
  await fetchDropdowns();
  
  if (wizardStore.selectedClassId) {
    await fetchClassSubjects(wizardStore.selectedClassId);
  }

  await wizardStore.refreshCurrentView();
  
  isInitializing.value = false;
});

const openAssignModal = async (payload: { slot: TimeSlot; schedule: ScheduleItem | null; classId?: number }) => {
  const targetClassId = payload.classId || wizardStore.selectedClassId;
  activeClassId.value = targetClassId;
  activeSlot.value = payload.slot;
  activeSchedule.value = payload.schedule;

  if (targetClassId) {
    await fetchClassSubjects(targetClassId);
  }

  showModal.value = true;
};

const handleSaveSlot = async (data: { 
  class_subject_teacher_id: number | null; 
  custom_activity: string | null; 
  is_locked: boolean; 
  apply_to_all_classes: boolean 
}) => {
  const targetClassId = activeClassId.value || wizardStore.selectedClassId;
  if (!activeSlot.value || !targetClassId || !wizardStore.selectedAcademicYearId) return;

  await wizardStore.manualAssign({
    academic_year_id: wizardStore.selectedAcademicYearId,
    class_id: targetClassId,
    time_slot_id: activeSlot.value.id,
    class_subject_teacher_id: data.class_subject_teacher_id,
    custom_activity: data.custom_activity,
    is_locked: data.is_locked,
    apply_to_all_classes: data.apply_to_all_classes,
  });

  showModal.value = false;
};

const handleClearSlot = async (payload?: { clear_all_classes?: boolean }) => {
  const targetClassId = activeClassId.value || wizardStore.selectedClassId;
  if (!activeSlot.value || !targetClassId || !wizardStore.selectedAcademicYearId) return;

  await wizardStore.clearSlot({
    academic_year_id: wizardStore.selectedAcademicYearId,
    class_id: targetClassId,
    time_slot_id: activeSlot.value.id,
    clear_all_classes: payload?.clear_all_classes ?? false,
  });

  showModal.value = false;
};

const handleGenerate = () => {
  const targetClassId = wizardStore.viewMode === 'master' ? null : wizardStore.selectedClassId;
  wizardStore.generateSchedule(targetClassId);
};

const handleReset = () => {
  const targetClassId = wizardStore.viewMode === 'master' ? null : wizardStore.selectedClassId;
  wizardStore.resetSchedule(targetClassId);
};

const handlePublish = () => {
  const targetClassId = wizardStore.viewMode === 'master' ? null : wizardStore.selectedClassId;
  wizardStore.publishSchedule(targetClassId);
};
</script>