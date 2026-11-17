import { defineStore } from 'pinia';
import { ref } from 'vue';
import { WizardService } from '../services/wizard.service';
import type {
  ClassMatrixData,
  TeacherMatrixData,
  MasterMatrixData,
  TimeSlot,
  LockSlotPayload,
  ManualAssignPayload,
  ClearSlotPayload,
  ScheduleSource
} from '../types/wizard';

export type WizardViewMode = 'class' | 'teacher' | 'master';

export const useWizardStore = defineStore('wizard', () => {
  const viewMode = ref<WizardViewMode>('class');
  const scheduleSource = ref<ScheduleSource>('draft');
  const selectedAcademicYearId = ref<number | null>(null);
  const selectedClassId = ref<number | null>(null);
  const selectedTeacherId = ref<number | null>(null);

  // Dibuat null secara default agar dinamis mengikuti slot profil waktu kelas yang terdaftar.
  const maxPeriodNumber = ref<number | null>(null);

  const timeSlots = ref<TimeSlot[]>([]);
  const classMatrix = ref<ClassMatrixData | null>(null);
  const teacherMatrix = ref<TeacherMatrixData | null>(null);
  const masterMatrix = ref<MasterMatrixData | null>(null);

  const isLoading = ref<boolean>(false);
  const isProcessing = ref<boolean>(false);
  const errorMessage = ref<string | null>(null);
  const successMessage = ref<string | null>(null);

  const fetchClassMatrix = async (classId: number, sourceOverride?: ScheduleSource) => {
    if (!classId) return;
    isLoading.value = true;
    errorMessage.value = null;

    const source = sourceOverride ?? scheduleSource.value;

    try {
      const response = await WizardService.getClassMatrix(
        classId,
        selectedAcademicYearId.value || undefined,
        source
      );
      if (response.status === 'success' && response.data) {
        classMatrix.value = response.data;
        timeSlots.value = response.data.time_slots || [];
      }
    } catch (error: any) {
      errorMessage.value = error.response?.data?.message || 'Gagal memuat matriks jadwal kelas.';
    } finally {
      isLoading.value = false;
    }
  };

  const fetchTeacherMatrix = async (teacherId: number, sourceOverride?: ScheduleSource) => {
    if (!teacherId || !selectedAcademicYearId.value) return;
    isLoading.value = true;
    errorMessage.value = null;

    const source = sourceOverride ?? scheduleSource.value;

    try {
      const response = await WizardService.getTeacherMatrix(
        teacherId,
        selectedAcademicYearId.value,
        source
      );
      if (response.status === 'success' && response.data) {
        teacherMatrix.value = response.data;
        timeSlots.value = response.data.time_slots || [];
      }
    } catch (error: any) {
      errorMessage.value = error.response?.data?.message || 'Gagal memuat matriks jadwal guru.';
    } finally {
      isLoading.value = false;
    }
  };

  const fetchMasterMatrix = async (sourceOverride?: ScheduleSource) => {
    if (!selectedAcademicYearId.value) return;
    isLoading.value = true;
    errorMessage.value = null;

    const source = sourceOverride ?? scheduleSource.value;

    try {
      const response = await WizardService.getMasterMatrix(selectedAcademicYearId.value, source);
      if (response.status === 'success' && response.data) {
        masterMatrix.value = response.data;
        timeSlots.value = response.data.time_slots || [];
      }
    } catch (error: any) {
      errorMessage.value = error.response?.data?.message || 'Gagal memuat matriks jadwal master.';
    } finally {
      isLoading.value = false;
    }
  };

  const refreshCurrentView = async () => {
    if (viewMode.value === 'class' && selectedClassId.value) {
      await fetchClassMatrix(selectedClassId.value);
    } else if (viewMode.value === 'teacher' && selectedTeacherId.value) {
      await fetchTeacherMatrix(selectedTeacherId.value);
    } else if (viewMode.value === 'master') {
      await fetchMasterMatrix();
    }
  };

  const generateSchedule = async (classIdOnly?: number | null) => {
    if (!selectedAcademicYearId.value) return { success: false, message: 'Tahun ajaran belum dipilih.' };

    isProcessing.value = true;
    errorMessage.value = null;
    successMessage.value = null;

    try {
      const response = await WizardService.generate({
        academic_year_id: selectedAcademicYearId.value,
        class_id: classIdOnly,
        max_period_number: maxPeriodNumber.value,
      });

      if (response.status === 'success') {
        const unplaced = response.data?.unplaced_count || 0;
        if (unplaced > 0) {
          successMessage.value = `Auto-generate selesai dengan ${unplaced} jam pelajaran yang belum mendapatkan slot.`;
        } else {
          successMessage.value = 'Auto-generate jadwal berhasil diselesaikan secara sempurna!';
        }
        await refreshCurrentView();
        return { success: true };
      }
      return { success: false, message: response.message };
    } catch (error: any) {
      const msg = error.response?.data?.message || 'Gagal melakukan auto-generate jadwal.';
      errorMessage.value = msg;
      return { success: false, message: msg };
    } finally {
      isProcessing.value = false;
    }
  };

  const lockSlot = async (payload: LockSlotPayload) => {
    isProcessing.value = true;
    errorMessage.value = null;

    try {
      const response = await WizardService.lockSlot(payload);
      if (response.status === 'success') {
        successMessage.value = 'Status kuncian slot berhasil diperbarui.';
        await refreshCurrentView();
        return { success: true };
      }
      return { success: false, message: response.message };
    } catch (error: any) {
      const msg = error.response?.data?.message || 'Gagal mengunci slot.';
      errorMessage.value = msg;
      return { success: false, message: msg };
    } finally {
      isProcessing.value = false;
    }
  };

  const manualAssign = async (payload: ManualAssignPayload) => {
    isProcessing.value = true;
    errorMessage.value = null;

    try {
      const response = await WizardService.manualAssign(payload);
      if (response.status === 'success') {
        successMessage.value = 'Jadwal berhasil diperbarui secara manual.';
        await refreshCurrentView();
        return { success: true };
      }
      return { success: false, message: response.message };
    } catch (error: any) {
      const msg = error.response?.data?.message || 'Gagal melakukan penempatan manual.';
      errorMessage.value = msg;
      return { success: false, message: msg };
    } finally {
      isProcessing.value = false;
    }
  };

  const clearSlot = async (payload: ClearSlotPayload) => {
    isProcessing.value = true;
    errorMessage.value = null;

    try {
      const response = await WizardService.clearSlot(payload);
      if (response.status === 'success') {
        successMessage.value = 'Slot jadwal berhasil dikosongkan.';
        await refreshCurrentView();
        return { success: true };
      }
      return { success: false, message: response.message };
    } catch (error: any) {
      const msg = error.response?.data?.message || 'Gagal mengosongkan slot.';
      errorMessage.value = msg;
      return { success: false, message: msg };
    } finally {
      isProcessing.value = false;
    }
  };

  const resetSchedule = async (classIdOnly?: number | null) => {
    if (!selectedAcademicYearId.value) return { success: false, message: 'Tahun ajaran belum dipilih.' };

    isProcessing.value = true;
    errorMessage.value = null;

    try {
      const response = await WizardService.reset({
        academic_year_id: selectedAcademicYearId.value,
        class_id: classIdOnly,
      });

      if (response.status === 'success') {
        successMessage.value = response.message || 'Jadwal yang tidak dikunci berhasil di-reset.';
        await refreshCurrentView();
        return { success: true };
      }
      return { success: false, message: response.message };
    } catch (error: any) {
      const msg = error.response?.data?.message || 'Gagal mereset jadwal.';
      errorMessage.value = msg;
      return { success: false, message: msg };
    } finally {
      isProcessing.value = false;
    }
  };

  const publishSchedule = async (classIdOnly?: number | null, force = false) => {
    if (!selectedAcademicYearId.value) return { success: false, message: 'Tahun ajaran belum dipilih.' };

    isProcessing.value = true;
    errorMessage.value = null;

    try {
      const response = await WizardService.publish({
        academic_year_id: selectedAcademicYearId.value,
        class_id: classIdOnly,
        force,
      });

      if (response.status === 'success') {
        if (response.data?.requires_confirmation) {
          return {
            success: true,
            requiresConfirmation: true,
            existingCount: response.data.existing_count,
          };
        }

        successMessage.value = response.message || 'Jadwal berhasil dipublikasikan ke Jadwal Resmi.';
        await refreshCurrentView();
        return { success: true, requiresConfirmation: false };
      }
      return { success: false, message: response.message };
    } catch (error: any) {
      const msg = error.response?.data?.message || 'Gagal mempublikasikan jadwal.';
      errorMessage.value = msg;
      return { success: false, message: msg };
    } finally {
      isProcessing.value = false;
    }
  };

  const deletePublishedSchedule = async () => {
    if (!selectedAcademicYearId.value) return { success: false, message: 'Tahun ajaran belum dipilih.' };

    isProcessing.value = true;
    errorMessage.value = null;

    try {
      const response = await WizardService.deletePublished({
        academic_year_id: selectedAcademicYearId.value,
      });

      if (response.status === 'success') {
        successMessage.value = response.message || 'Seluruh jadwal resmi berhasil dihapus.';
        await refreshCurrentView();
        return { success: true };
      }
      return { success: false, message: response.message };
    } catch (error: any) {
      const msg = error.response?.data?.message || 'Gagal menghapus jadwal resmi.';
      errorMessage.value = msg;
      return { success: false, message: msg };
    } finally {
      isProcessing.value = false;
    }
  };

  return {
    viewMode,
    scheduleSource,
    selectedAcademicYearId,
    selectedClassId,
    selectedTeacherId,
    maxPeriodNumber,
    timeSlots,
    classMatrix,
    teacherMatrix,
    masterMatrix,
    isLoading,
    isProcessing,
    errorMessage,
    successMessage,
    fetchClassMatrix,
    fetchTeacherMatrix,
    fetchMasterMatrix,
    refreshCurrentView,
    generateSchedule,
    lockSlot,
    manualAssign,
    clearSlot,
    resetSchedule,
    publishSchedule,
    deletePublishedSchedule,
  };
});