<template>
  <div class="login-container min-vh-100 d-flex align-items-center justify-content-center p-3">
    <div class="card card-login border-0 shadow-sm rounded-4 bg-white p-4 p-sm-5 w-100" style="max-width: 400px;">

      <!-- Brand Header -->
      <div class="text-center mb-4">
        <div class="brand-icon mx-auto mb-3 d-flex align-items-center justify-content-center text-primary rounded-3">
          <i class="bi bi-calendar-check-fill fs-3"></i>
        </div>
        <h4 class="fw-bold text-dark mb-1">Portal Administrator</h4>
        <p class="text-secondary small mb-0">Sistem Penjadwalan Sekolah</p>
      </div>

      <!-- Alert Pesan Error -->
      <div v-if="authStore.errorMessage" class="alert alert-danger border-0 rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-3" role="alert">
        <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
        <div>{{ authStore.errorMessage }}</div>
      </div>

      <!-- Form Login -->
      <form @submit.prevent="handleLogin">
        <div class="mb-3">
          <label for="email" class="form-label fw-medium text-dark small">Email</label>
          <input 
            type="email" 
            class="form-control rounded-3 py-2 px-3 shadow-none" 
            id="email" 
            v-model="form.email" 
            placeholder="admin@sekolah.sch.id"
            required
          >
        </div>

        <div class="mb-4">
          <label for="password" class="form-label fw-medium text-dark small">Kata Sandi</label>
          <input 
            type="password" 
            class="form-control rounded-3 py-2 px-3 shadow-none" 
            id="password" 
            v-model="form.password" 
            placeholder="••••••••"
            required
          >
        </div>

        <button 
          type="submit" 
          class="btn btn-primary w-100 rounded-3 py-2 fw-semibold d-flex justify-content-center align-items-center gap-2 mb-3"
          :disabled="authStore.isLoading"
        >
          <span v-if="!authStore.isLoading">Masuk</span>
          <span v-else class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        </button>
      </form>

      <!-- Footer Note -->
      <div class="text-center pt-3 border-top mt-2">
        <small class="text-muted" style="font-size: 0.78rem;">Kendala akses? Hubungi Tim IT Sekolah</small>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
  email: '',
  password: '',
});

const handleLogin = async () => {
  const result = await authStore.login(form);
  if (result.success) {
    router.push('/admin/dashboard');
  }
};
</script>

<style scoped>
.login-container {
  background-color: #f1f5f9;
}

.brand-icon {
  width: 52px;
  height: 52px;
  background-color: #eff6ff;
  border: 1px solid #dbeafe;
}

.form-control {
  border-color: #cbd5e1;
  font-size: 0.9rem;
}

.form-control:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
}

.btn-primary {
  background-color: #2563eb;
  border-color: #2563eb;
  transition: background-color 0.15s ease;
}

.btn-primary:hover {
  background-color: #1d4ed8;
  border-color: #1d4ed8;
}
</style>