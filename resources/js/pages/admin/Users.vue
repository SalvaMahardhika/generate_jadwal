<template>
  <SidebarAdmin>
    <div class="container-fluid px-0">
      
      <!-- Page Header -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4 pb-2 border-bottom">
        <div>
          <h5 class="fw-bold text-dark mb-1">Manajemen Pengguna</h5>
          <p class="text-secondary small mb-0">Kelola akun pengguna, email, nomor telepon, dan status akses sistem.</p>
        </div>
        <button 
          type="button" 
          class="btn btn-primary rounded-3 py-2 px-3 small fw-medium d-flex align-items-center gap-2"
          @click="openCreateModal()"
        >
          <i class="bi bi-person-plus-fill"></i>
          <span>Tambah Pengguna</span>
        </button>
      </div>

      <!-- Alert Pesan Sukses -->
      <div v-if="userStore.successMessage" class="alert alert-success border-0 rounded-3 py-2 px-3 small d-flex align-items-center justify-content-between mb-3" role="alert">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill"></i>
          <span>{{ userStore.successMessage }}</span>
        </div>
        <button type="button" class="btn-close small" @click="userStore.successMessage = null"></button>
      </div>

      <!-- Filter Bar -->
      <div class="card border rounded-3 p-3 bg-white mb-4">
        <div class="row g-2 align-items-center">
          <div class="col-12 col-md-5">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light border-end-0 text-muted">
                <i class="bi bi-search"></i>
              </span>
              <input 
                type="text" 
                class="form-control bg-light border-start-0 shadow-none" 
                placeholder="Cari nama, email, atau no. HP..." 
                v-model="userStore.filters.search"
                @input="userStore.fetchUsers()"
              >
            </div>
          </div>

          <div class="col-12 col-md-4">
            <select 
              class="form-select form-select-sm bg-light shadow-none" 
              v-model="userStore.filters.status"
              @change="userStore.fetchUsers()"
            >
              <option value="">Semua Status</option>
              <option value="active">Aktif</option>
              <option value="inactive">Nonaktif</option>
            </select>
          </div>

          <div class="col-12 col-md-3 text-md-end" v-if="userStore.filters.search || userStore.filters.status">
            <button 
              type="button" 
              class="btn btn-link text-decoration-none text-muted small p-0"
              @click="userStore.resetFilters()"
            >
              Reset Filter
            </button>
          </div>
        </div>
      </div>

      <!-- Table Users -->
      <div class="card border rounded-3 bg-white overflow-hidden shadow-sm">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="bg-light border-bottom">
              <tr>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small" style="width: 60px;">#</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Pengguna</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Email</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">No. Telepon</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Status</th>
                <th scope="col" class="py-3 px-3 text-muted fw-semibold small text-end" style="width: 120px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="userStore.isLoading">
                <td colspan="6" class="text-center py-5 text-muted small">
                  <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                  Memuat data pengguna...
                </td>
              </tr>

              <tr v-else-if="userStore.users.length === 0">
                <td colspan="6" class="text-center py-5 text-muted small">
                  <i class="bi bi-people fs-3 d-block mb-2 text-secondary opacity-50"></i>
                  Data pengguna tidak ditemukan.
                </td>
              </tr>

              <tr v-else v-for="(user, index) in userStore.users" :key="user.id">
                <td class="px-3 small text-secondary">{{ index + 1 }}</td>
                <td class="px-3">
                  <div class="d-flex align-items-center gap-2.5">
                    <div class="avatar-circle rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center fs-7 flex-shrink-0">
                      {{ getInitials(user.name) }}
                    </div>
                    <div>
                      <div class="fw-bold text-dark small mb-0">{{ user.name }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-3 small text-dark font-monospace">{{ user.email }}</td>
                <td class="px-3 small text-secondary">
                  {{ user.phone || '-' }}
                </td>
                <td class="px-3 small">
                  <span 
                    class="badge rounded-pill px-2.5 py-1 fs-8 fw-semibold"
                    :class="user.status === 'active' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle'"
                  >
                    {{ user.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="px-3 text-end">
                  <div class="d-flex justify-content-end gap-1">
                    <button 
                      type="button" 
                      class="btn btn-light btn-sm text-secondary border-0 rounded-2 px-2 py-1"
                      title="Edit"
                      @click="openEditModal(user)"
                    >
                      <i class="bi bi-pencil-square fs-7"></i>
                    </button>
                    <button 
                      type="button" 
                      class="btn btn-light btn-sm text-danger border-0 rounded-2 px-2 py-1"
                      title="Hapus"
                      @click="openDeleteModal(user)"
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

    <!-- Modal Form (Tambah / Edit) -->
    <Teleport to="body">
      <div v-if="showFormModal" class="modal-backdrop fade show"></div>
      <div v-if="showFormModal" class="modal fade show d-block" tabindex="-1" @click.self="closeFormModal">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            
            <div class="modal-header border-bottom px-4 py-3">
              <h6 class="modal-title fw-bold text-dark">{{ isEditing ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}</h6>
              <button type="button" class="btn-close small shadow-none" @click="closeFormModal" :disabled="userStore.isSaving"></button>
            </div>

            <form @submit.prevent="submitForm">
              <div class="modal-body p-4">
                
                <!-- Nama Lengkap -->
                <div class="mb-3">
                  <label for="name" class="form-label fw-medium text-dark small">Nama Lengkap <span class="text-danger">*</span></label>
                  <input 
                    type="text" 
                    id="name" 
                    class="form-control form-control-sm rounded-3 shadow-none"
                    :class="{ 'is-invalid': formErrors.name }"
                    placeholder="Contoh: Administrator Utama"
                    v-model="form.name"
                    required
                  >
                  <div v-if="formErrors.name" class="invalid-feedback small">{{ formErrors.name[0] }}</div>
                </div>

                <!-- Email & No. HP -->
                <div class="row g-3 mb-3">
                  <div class="col-12 col-sm-6">
                    <label for="email" class="form-label fw-medium text-dark small">Alamat Email <span class="text-danger">*</span></label>
                    <input 
                      type="email" 
                      id="email" 
                      class="form-control form-control-sm rounded-3 shadow-none"
                      :class="{ 'is-invalid': formErrors.email }"
                      placeholder="admin@sekolah.sch.id"
                      v-model="form.email"
                      required
                    >
                    <div v-if="formErrors.email" class="invalid-feedback small">{{ formErrors.email[0] }}</div>
                  </div>

                  <div class="col-12 col-sm-6">
                    <label for="phone" class="form-label fw-medium text-dark small">No. Telepon (Opsional)</label>
                    <input 
                      type="text" 
                      id="phone" 
                      class="form-control form-control-sm rounded-3 shadow-none"
                      :class="{ 'is-invalid': formErrors.phone }"
                      placeholder="08123456789"
                      v-model="form.phone"
                    >
                    <div v-if="formErrors.phone" class="invalid-feedback small">{{ formErrors.phone[0] }}</div>
                  </div>
                </div>

                <!-- Kata Sandi -->
                <div class="mb-3">
                  <label for="password" class="form-label fw-medium text-dark small">
                    Kata Sandi <span v-if="!isEditing" class="text-danger">*</span>
                    <small v-else class="text-muted font-normal">(Kosongkan jika tidak ingin diubah)</small>
                  </label>
                  <div class="input-group input-group-sm">
                    <input 
                      :type="showPassword ? 'text' : 'password'" 
                      id="password" 
                      class="form-control rounded-start-3 shadow-none"
                      :class="{ 'is-invalid': formErrors.password }"
                      placeholder="Minimal 8 karakter"
                      v-model="form.password"
                      :required="!isEditing"
                    >
                    <button 
                      type="button" 
                      class="btn btn-outline-secondary rounded-end-3"
                      @click="showPassword = !showPassword"
                    >
                      <i class="bi" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                    </button>
                    <div v-if="formErrors.password" class="invalid-feedback small">{{ formErrors.password[0] }}</div>
                  </div>
                </div>

                <!-- Status Pengguna -->
                <div class="mb-2">
                  <label for="status" class="form-label fw-medium text-dark small">Status Akun</label>
                  <select 
                    id="status" 
                    class="form-select form-select-sm rounded-3 shadow-none"
                    :class="{ 'is-invalid': formErrors.status }"
                    v-model="form.status"
                  >
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                  </select>
                  <div v-if="formErrors.status" class="invalid-feedback small">{{ formErrors.status[0] }}</div>
                </div>

              </div>

              <div class="modal-footer border-top px-4 py-3 bg-light">
                <button 
                  type="button" 
                  class="btn btn-light rounded-3 py-2 px-3 small fw-medium border shadow-none"
                  @click="closeFormModal"
                  :disabled="userStore.isSaving"
                >
                  Batal
                </button>
                <button 
                  type="submit" 
                  class="btn btn-primary rounded-3 py-2 px-3 small fw-semibold shadow-none d-flex align-items-center gap-2"
                  :disabled="userStore.isSaving"
                >
                  <span v-if="!userStore.isSaving">Simpan</span>
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
              
              <h6 class="fw-bold text-dark mb-1">Hapus Pengguna?</h6>
              <p class="text-secondary small mb-3">
                Apakah Anda yakin ingin menghapus pengguna <strong>{{ selectedUser?.name }}</strong>?
              </p>

              <div v-if="deleteErrorMessage" class="alert alert-danger border-0 rounded-3 py-2 px-3 small text-start mb-3" role="alert">
                {{ deleteErrorMessage }}
              </div>

              <div class="d-flex gap-2">
                <button 
                  type="button" 
                  class="btn btn-light w-50 rounded-3 py-2 small fw-medium border shadow-none"
                  @click="closeDeleteModal"
                  :disabled="userStore.isSaving"
                >
                  Batal
                </button>
                <button 
                  type="button" 
                  class="btn btn-danger w-50 rounded-3 py-2 small fw-semibold shadow-none d-flex align-items-center justify-content-center gap-2"
                  @click="confirmDelete"
                  :disabled="userStore.isSaving"
                >
                  <span v-if="!userStore.isSaving">Hapus</span>
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
import { ref, reactive, onMounted } from 'vue';
import SidebarAdmin from '../../layouts/SidebarAdmin.vue';
import { useUserStore } from '../../stores/user';
import type { User, UserPayload } from '../../types/user';

const userStore = useUserStore();

const showFormModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const showPassword = ref(false);
const selectedUser = ref<User | null>(null);
const deleteErrorMessage = ref<string | null>(null);

const form = reactive<UserPayload>({
  name: '',
  email: '',
  password: '',
  phone: '',
  status: 'active',
});

const formErrors = reactive<Record<string, string[]>>({});

onMounted(() => {
  userStore.fetchUsers();
});

const getInitials = (name: string) => {
  if (!name) return 'U';
  const parts = name.trim().split(' ');
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return parts[0].substring(0, 2).toUpperCase();
};

const resetForm = () => {
  form.name = '';
  form.email = '';
  form.password = '';
  form.phone = '';
  form.status = 'active';
  showPassword.value = false;
  Object.keys(formErrors).forEach(key => delete formErrors[key]);
};

const openCreateModal = () => {
  resetForm();
  isEditing.value = false;
  selectedUser.value = null;
  showFormModal.value = true;
};

const openEditModal = (user: User) => {
  resetForm();
  isEditing.value = true;
  selectedUser.value = user;

  form.name = user.name;
  form.email = user.email;
  form.phone = user.phone || '';
  form.status = user.status;
  form.password = ''; // Biarkan kosong saat edit

  showFormModal.value = true;
};

const closeFormModal = () => {
  if (!userStore.isSaving) {
    showFormModal.value = false;
  }
};

const submitForm = async () => {
  Object.keys(formErrors).forEach(key => delete formErrors[key]);

  const payload: UserPayload = {
    name: form.name.trim(),
    email: form.email.trim(),
    phone: form.phone ? form.phone.trim() : null,
    status: form.status,
  };

  if (form.password) {
    payload.password = form.password;
  }

  let result;
  if (isEditing.value && selectedUser.value) {
    result = await userStore.updateUser(selectedUser.value.id, payload);
  } else {
    result = await userStore.createUser(payload);
  }

  if (result.success) {
    showFormModal.value = false;
  } else if (result.errors) {
    Object.assign(formErrors, result.errors);
  }
};

const openDeleteModal = (user: User) => {
  selectedUser.value = user;
  deleteErrorMessage.value = null;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  if (!userStore.isSaving) {
    showDeleteModal.value = false;
  }
};

const confirmDelete = async () => {
  if (!selectedUser.value) return;

  deleteErrorMessage.value = null;
  const result = await userStore.deleteUser(selectedUser.value.id);

  if (result.success) {
    showDeleteModal.value = false;
  } else {
    deleteErrorMessage.value = result.message || 'Gagal menghapus pengguna.';
  }
};
</script>

<style scoped>
.avatar-circle {
  width: 38px;
  height: 38px;
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
.bg-success-subtle {
  background-color: #f0fdf4 !important;
  color: #16a34a !important;
}
.bg-secondary-subtle {
  background-color: #f8fafc !important;
  color: #64748b !important;
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