import { defineStore } from 'pinia';
import { ref, reactive } from 'vue';
import { ClassService } from '../services/class.service';
import type { ClassItem, ClassPayload, ClassFilter } from '../types/class';

export const useClassStore = defineStore('class', () => {
  const classes = ref<ClassItem[]>([]);
  const isLoading = ref<boolean>(false);
  const isSaving = ref<boolean>(false);
  const errorMessage = ref<string | null>(null);
  const successMessage = ref<string | null>(null);

  const filters = reactive<ClassFilter>({
    search: '',
    academic_year_id: '',
    grade_id: '',
    status: '',
  });

  const fetchClasses = async () => {
    isLoading.value = true;
    errorMessage.value = null;

    try {
      const response = await ClassService.getAll(filters);
      if (response.status === 'success' && response.data) {
        classes.value = response.data;
      }
    } catch (error: any) {
      errorMessage.value = error.response?.data?.message || 'Gagal memuat data kelas.';
    } finally {
      isLoading.value = false;
    }
  };

  const createClass = async (payload: ClassPayload) => {
    isSaving.value = true;
    errorMessage.value = null;
    successMessage.value = null;

    try {
      const response = await ClassService.create(payload);
      if (response.status === 'success') {
        successMessage.value = response.message || 'Kelas berhasil ditambahkan.';
        await fetchClasses();
        return { success: true };
      }
      return { success: false, message: response.message };
    } catch (error: any) {
      const msg = error.response?.data?.message || 'Gagal menambahkan kelas.';
      errorMessage.value = msg;
      return { success: false, message: msg, errors: error.response?.data?.errors };
    } finally {
      isSaving.value = false;
    }
  };

  const updateClass = async (id: number, payload: ClassPayload) => {
    isSaving.value = true;
    errorMessage.value = null;
    successMessage.value = null;

    try {
      const response = await ClassService.update(id, payload);
      if (response.status === 'success') {
        successMessage.value = response.message || 'Kelas berhasil diperbarui.';
        await fetchClasses();
        return { success: true };
      }
      return { success: false, message: response.message };
    } catch (error: any) {
      const msg = error.response?.data?.message || 'Gagal memperbarui kelas.';
      errorMessage.value = msg;
      return { success: false, message: msg, errors: error.response?.data?.errors };
    } finally {
      isSaving.value = false;
    }
  };

  const deleteClass = async (id: number) => {
    isSaving.value = true;
    errorMessage.value = null;

    try {
      const response = await ClassService.delete(id);
      if (response.status === 'success') {
        successMessage.value = response.message || 'Kelas berhasil dihapus.';
        await fetchClasses();
        return { success: true };
      }
      return { success: false, message: response.message };
    } catch (error: any) {
      const msg = error.response?.data?.message || 'Gagal menghapus kelas.';
      errorMessage.value = msg;
      return { success: false, message: msg };
    } finally {
      isSaving.value = false;
    }
  };

  const resetFilters = () => {
    filters.search = '';
    filters.academic_year_id = '';
    filters.grade_id = '';
    filters.status = '';
    fetchClasses();
  };

  return {
    classes,
    isLoading,
    isSaving,
    errorMessage,
    successMessage,
    filters,
    fetchClasses,
    createClass,
    updateClass,
    deleteClass,
    resetFilters,
  };
});