<template>
  <div class="d-flex min-vh-100 bg-app">
    <!-- Sidebar -->
    <aside class="sidebar bg-white border-end d-flex flex-column justify-content-between p-3 flex-shrink-0">
      <div>
        <!-- App Logo / Title -->
        <div class="d-flex align-items-center gap-2 px-2 py-3 mb-3 border-bottom">
          <div class="bg-primary text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
            <i class="bi bi-calendar-check-fill fs-6"></i>
          </div>
          <div>
            <h6 class="fw-bold text-dark mb-0 fs-6">Sistem Jadwal</h6>
            <small class="text-muted" style="font-size: 0.72rem;">Automated Timetabling</small>
          </div>
        </div>

        <!-- Navigation Links Group -->
        <nav class="nav flex-column gap-1">
          <!-- GROUP: UTAMA -->
          <div class="menu-label text-uppercase text-muted fw-bold px-2 mt-2 mb-1">Utama</div>
          <router-link to="/admin/dashboard" class="nav-link-item" active-class="active">
            <i class="bi bi-grid-1x2-fill me-2"></i>
            <span>Dashboard</span>
          </router-link>
          <router-link to="/admin/wizard" class="nav-link-item" active-class="active">
            <i class="bi bi-magic me-2"></i>
            <span>Wizard Penjadwalan</span>
          </router-link>
          <router-link to="/admin/schedules" class="nav-link-item" active-class="active">
            <i class="bi bi-calendar-event-fill me-2"></i>
            <span>Jadwal Resmi</span>
          </router-link>

          <!-- GROUP: AKADEMIK & PEMETAAN -->
          <div class="menu-label text-uppercase text-muted fw-bold px-2 mt-3 mb-1">Akademik & Pemetaan</div>
          <router-link to="/admin/plotting" class="nav-link-item" active-class="active">
            <i class="bi bi-diagram-3-fill me-2"></i>
            <span>Plotting Mapel & Guru</span>
          </router-link>
          <router-link to="/admin/classes" class="nav-link-item" active-class="active">
            <i class="bi bi-building-fill me-2"></i>
            <span>Manajemen Kelas</span>
          </router-link>

          <!-- GROUP: MASTER DATA -->
          <div class="menu-label text-uppercase text-muted fw-bold px-2 mt-3 mb-1">Master Data</div>
          <router-link to="/admin/teachers" class="nav-link-item" active-class="active">
            <i class="bi bi-person-badge-fill me-2"></i>
            <span>Data Guru</span>
          </router-link>
          <router-link to="/admin/subjects" class="nav-link-item" active-class="active">
            <i class="bi bi-book-half me-2"></i>
            <span>Mata Pelajaran</span>
          </router-link>
          <router-link to="/admin/time-slots" class="nav-link-item" active-class="active">
            <i class="bi bi-clock-history me-2"></i>
            <span>Jam & Slot Waktu</span>
          </router-link>
          <router-link to="/admin/academic-years-majors" class="nav-link-item" active-class="active">
            <i class="bi bi-calendar3 me-2"></i>
            <span>Tahun Ajaran & Jurusan</span>
          </router-link>

          <!-- GROUP: PENGATURAN -->
          <div class="menu-label text-uppercase text-muted fw-bold px-2 mt-3 mb-1">Pengaturan</div>
          <router-link to="/admin/users" class="nav-link-item" active-class="active">
            <i class="bi bi-people-fill me-2"></i>
            <span>Data Pengguna</span>
          </router-link>
        </nav>
      </div>

      <!-- User Info & Logout Button -->
      <div class="pt-3 border-top mt-3">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2 overflow-hidden">
            <div class="avatar bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold">
              {{ userInitial }}
            </div>
            <div class="text-truncate">
              <p class="fw-semibold text-dark mb-0 fs-7 text-truncate">{{ authStore.user?.name || 'Administrator' }}</p>
              <small class="text-muted text-truncate d-block" style="font-size: 0.7rem;">{{ authStore.user?.email }}</small>
            </div>
          </div>
          <button 
            type="button"
            @click="openLogoutModal" 
            class="btn btn-light btn-sm text-danger border-0 rounded-circle p-2" 
            title="Keluar"
          >
            <i class="bi bi-box-arrow-right fs-6"></i>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-grow-1 d-flex flex-column overflow-hidden">
      <!-- Navbar / Top Header -->
      <header class="bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
        <div>
          <h5 class="fw-bold text-dark mb-0 fs-6">Panel Administrator</h5>
        </div>
        <div class="d-flex align-items-center gap-3">
          <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-medium fs-8">
            <i class="bi bi-circle-fill me-1 fs-9"></i> Sistem Siap
          </span>
        </div>
      </header>

      <!-- Page Slot Content -->
      <main class="flex-grow-1 p-3 p-md-4 overflow-y-auto">
        <slot />
      </main>
    </div>

    <!-- Modal Mandiri Konfirmasi Logout -->
    <Teleport to="body">
      <div v-if="showLogoutModal" class="modal-backdrop fade show"></div>
      <div 
        v-if="showLogoutModal" 
        class="modal fade show d-block" 
        tabindex="-1"
        @click.self="closeLogoutModal"
      >
        <div class="modal-dialog modal-dialog-centered modal-sm">
          <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="modal-body text-center p-4">
              
              <div class="logout-icon mx-auto mb-3 text-danger bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-box-arrow-right fs-4"></i>
              </div>
              
              <h6 class="fw-bold text-dark mb-1">Konfirmasi Keluar</h6>
              <p class="text-secondary small mb-4">Apakah Anda yakin ingin keluar dari sistem?</p>

              <div class="d-flex gap-2">
                <button 
                  type="button" 
                  class="btn btn-light w-50 rounded-3 py-2 small fw-medium border shadow-none"
                  @click="closeLogoutModal"
                  :disabled="isLoggingOut"
                >
                  Batal
                </button>
                <button 
                  type="button" 
                  class="btn btn-danger w-50 rounded-3 py-2 small fw-semibold shadow-none d-flex align-items-center justify-content-center gap-2"
                  @click="confirmLogout"
                  :disabled="isLoggingOut"
                >
                  <span v-if="!isLoggingOut">Keluar</span>
                  <span v-else class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                </button>
              </div>

            </div>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const showLogoutModal = ref(false);
const isLoggingOut = ref(false);

const userInitial = computed(() => {
  const name = authStore.user?.name || 'Admin';
  return name.charAt(0).toUpperCase();
});

const openLogoutModal = () => {
  showLogoutModal.value = true;
};

const closeLogoutModal = () => {
  if (!isLoggingOut.value) {
    showLogoutModal.value = false;
  }
};

const confirmLogout = async () => {
  isLoggingOut.value = true;
  try {
    await authStore.logout();
    showLogoutModal.value = false;
    router.push('/login');
  } catch (error) {
    console.error('Logout error:', error);
  } finally {
    isLoggingOut.value = false;
  }
};
</script>

<style scoped>
.bg-app {
  background-color: #f8fafc;
}
.sidebar {
  width: 250px;
  min-height: 100vh;
}
.menu-label {
  font-size: 0.68rem;
  letter-spacing: 0.04em;
}
.nav-link-item {
  display: flex;
  align-items: center;
  padding: 8px 12px;
  border-radius: 8px;
  color: #64748b;
  font-weight: 500;
  font-size: 0.85rem;
  text-decoration: none;
  transition: all 0.15s ease;
}
.nav-link-item:hover {
  background-color: #f1f5f9;
  color: #0f172a;
}
.nav-link-item.active {
  background-color: #eff6ff;
  color: #2563eb;
  font-weight: 600;
}
.avatar {
  width: 34px;
  height: 34px;
  font-size: 0.85rem;
}
.logout-icon {
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
.fs-7 { font-size: 0.82rem; }
.fs-8 { font-size: 0.75rem; }
.fs-9 { font-size: 0.5rem; }
</style>