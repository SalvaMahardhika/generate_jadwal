import { defineStore } from 'pinia';
import { ref, reactive } from 'vue';
import { UserService } from '../services/user.service';
import type { User, UserPayload, UserFilter } from '../types/user';

export const useUserStore = defineStore('user', () => {
  const users = ref<User[]>([]);
  const isLoading = ref<boolean>(false);
  const isSaving = ref<boolean>(false);
  const errorMessage = ref<string | null>(null);
  const successMessage = ref<string | null>(null);

  const filters = reactive<UserFilter>({
    search: '',
    status: '',
  });

  const fetchUsers = async () => {
    isLoading.value = true;
    errorMessage.value = null;

    try {
      const response = await UserService.getAll(filters);
      if (response.status === 'success' && response.data) {
        users.value = response.data;
      }
    } catch (error: any) {
      errorMessage.value = error.response?.data?.message || 'Gagal memuat data pengguna.';
    } finally {
      isLoading.value = false;
    }
  };

  const createUser = async (payload: UserPayload) => {
    isSaving.value = true;
    errorMessage.value = null;
    successMessage.value = null;

    try {
      const response = await UserService.create(payload);
      if (response.status === 'success') {
        successMessage.value = response.message || 'Pengguna berhasil ditambahkan.';
        await fetchUsers();
        return { success: true };
      }
      return { success: false, message: response.message };
    } catch (error: any) {
      const msg = error.response?.data?.message || 'Gagal menambahkan pengguna.';
      errorMessage.value = msg;
      return { success: false, message: msg, errors: error.response?.data?.errors };
    } finally {
      isSaving.value = false;
    }
  };

  const updateUser = async (id: number, payload: UserPayload) => {
    isSaving.value = true;
    errorMessage.value = null;
    successMessage.value = null;

    try {
      const response = await UserService.update(id, payload);
      if (response.status === 'success') {
        successMessage.value = response.message || 'Data pengguna berhasil diperbarui.';
        await fetchUsers();
        return { success: true };
      }
      return { success: false, message: response.message };
    } catch (error: any) {
      const msg = error.response?.data?.message || 'Gagal memperbarui pengguna.';
      errorMessage.value = msg;
      return { success: false, message: msg, errors: error.response?.data?.errors };
    } finally {
      isSaving.value = false;
    }
  };

  const deleteUser = async (id: number) => {
    isSaving.value = true;
    errorMessage.value = null;

    try {
      const response = await UserService.delete(id);
      if (response.status === 'success') {
        successMessage.value = response.message || 'Pengguna berhasil dihapus.';
        await fetchUsers();
        return { success: true };
      }
      return { success: false, message: response.message };
    } catch (error: any) {
      const msg = error.response?.data?.message || 'Gagal menghapus pengguna.';
      errorMessage.value = msg;
      return { success: false, message: msg };
    } finally {
      isSaving.value = false;
    }
  };

  const resetFilters = () => {
    filters.search = '';
    filters.status = '';
    fetchUsers();
  };

  return {
    users,
    isLoading,
    isSaving,
    errorMessage,
    successMessage,
    filters,
    fetchUsers,
    createUser,
    updateUser,
    deleteUser,
    resetFilters,
  };
});