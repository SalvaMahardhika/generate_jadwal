import { defineStore } from 'pinia';
import { ref, reactive } from 'vue';
import { TimeSlotService } from '../services/timeSlot.service';
import type { TimeSlot, TimeSlotPayload, TimeSlotFilter } from '../types/timeSlot';

export const useTimeSlotStore = defineStore('timeSlot', () => {
  const timeSlots = ref<TimeSlot[]>([]);
  const isLoading = ref<boolean>(false);
  const isSaving = ref<boolean>(false);
  const errorMessage = ref<string | null>(null);
  const successMessage = ref<string | null>(null);

  const filters = reactive<TimeSlotFilter>({
    day: '',
    is_break: '',
  });

  const fetchTimeSlots = async () => {
    isLoading.value = true;
    errorMessage.value = null;

    try {
      const response = await TimeSlotService.getAll(filters);
      if (response.status === 'success' && response.data) {
        timeSlots.value = response.data;
      }
    } catch (error: any) {
      errorMessage.value = error.response?.data?.message || 'Gagal memuat data slot waktu.';
    } finally {
      isLoading.value = false;
    }
  };

  const createTimeSlot = async (payload: TimeSlotPayload) => {
    isSaving.value = true;
    errorMessage.value = null;
    successMessage.value = null;

    try {
      const response = await TimeSlotService.create(payload);
      if (response.status === 'success') {
        successMessage.value = response.message || 'Slot waktu berhasil ditambahkan.';
        await fetchTimeSlots();
        return { success: true };
      }
      return { success: false, message: response.message };
    } catch (error: any) {
      const msg = error.response?.data?.message || 'Gagal menambahkan slot waktu.';
      errorMessage.value = msg;
      return { success: false, message: msg, errors: error.response?.data?.errors };
    } finally {
      isSaving.value = false;
    }
  };

  const updateTimeSlot = async (id: number, payload: TimeSlotPayload) => {
    isSaving.value = true;
    errorMessage.value = null;
    successMessage.value = null;

    try {
      const response = await TimeSlotService.update(id, payload);
      if (response.status === 'success') {
        successMessage.value = response.message || 'Slot waktu berhasil diperbarui.';
        await fetchTimeSlots();
        return { success: true };
      }
      return { success: false, message: response.message };
    } catch (error: any) {
      const msg = error.response?.data?.message || 'Gagal memperbarui slot waktu.';
      errorMessage.value = msg;
      return { success: false, message: msg, errors: error.response?.data?.errors };
    } finally {
      isSaving.value = false;
    }
  };

  const deleteTimeSlot = async (id: number) => {
    isSaving.value = true;
    errorMessage.value = null;

    try {
      const response = await TimeSlotService.delete(id);
      if (response.status === 'success') {
        successMessage.value = response.message || 'Slot waktu berhasil dihapus.';
        await fetchTimeSlots();
        return { success: true };
      }
      return { success: false, message: response.message };
    } catch (error: any) {
      const msg = error.response?.data?.message || 'Gagal menghapus slot waktu.';
      errorMessage.value = msg;
      return { success: false, message: msg };
    } finally {
      isSaving.value = false;
    }
  };

  const resetFilters = () => {
    filters.day = '';
    filters.is_break = '';
    fetchTimeSlots();
  };

  return {
    timeSlots,
    isLoading,
    isSaving,
    errorMessage,
    successMessage,
    filters,
    fetchTimeSlots,
    createTimeSlot,
    updateTimeSlot,
    deleteTimeSlot,
    resetFilters,
  };
});