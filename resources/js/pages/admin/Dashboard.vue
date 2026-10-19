<template>
  <SidebarAdmin>
    <div class="container-fluid px-0">
      
      <!-- Page Header -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4 pb-2 border-bottom">
        <div>
          <h5 class="fw-bold text-dark mb-1">Dashboard</h5>
          <p class="text-secondary small mb-0">Ringkasan data master dan status sistem penjadwalan sekolah.</p>
        </div>
        <router-link to="/admin/wizard" class="btn btn-primary rounded-3 py-2 px-3 small fw-medium d-flex align-items-center gap-2">
          <i class="bi bi-magic"></i>
          <span>Wizard Penjadwalan</span>
        </router-link>
      </div>

      <!-- Stat Cards Grid -->
      <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
        <!-- Total Guru -->
        <div class="col">
          <div class="card border rounded-3 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small fw-medium text-uppercase" style="font-size: 0.7rem;">Total Guru</span>
                <h4 class="fw-bold text-dark mb-0 mt-1">
                  <span v-if="isLoading" class="spinner-border spinner-border-sm text-primary" role="status"></span>
                  <span v-else>{{ stats.teachers }}</span>
                </h4>
              </div>
              <div class="stat-icon text-primary bg-primary-subtle rounded-3 d-flex align-items-center justify-content-center">
                <i class="bi bi-person-badge fs-5"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Total Kelas -->
        <div class="col">
          <div class="card border rounded-3 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small fw-medium text-uppercase" style="font-size: 0.7rem;">Total Kelas</span>
                <h4 class="fw-bold text-dark mb-0 mt-1">
                  <span v-if="isLoading" class="spinner-border spinner-border-sm text-primary" role="status"></span>
                  <span v-else>{{ stats.classes }}</span>
                </h4>
              </div>
              <div class="stat-icon text-primary bg-primary-subtle rounded-3 d-flex align-items-center justify-content-center">
                <i class="bi bi-building fs-5"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Total Mapel -->
        <div class="col">
          <div class="card border rounded-3 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small fw-medium text-uppercase" style="font-size: 0.7rem;">Mata Pelajaran</span>
                <h4 class="fw-bold text-dark mb-0 mt-1">
                  <span v-if="isLoading" class="spinner-border spinner-border-sm text-primary" role="status"></span>
                  <span v-else>{{ stats.subjects }}</span>
                </h4>
              </div>
              <div class="stat-icon text-primary bg-primary-subtle rounded-3 d-flex align-items-center justify-content-center">
                <i class="bi bi-book fs-5"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Tahun Ajaran -->
        <div class="col">
          <div class="card border rounded-3 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small fw-medium text-uppercase" style="font-size: 0.7rem;">Tahun Ajaran</span>
                <h6 class="fw-bold text-dark mb-0 mt-1 fs-6 text-truncate">
                  <span v-if="isLoading" class="spinner-border spinner-border-sm text-primary" role="status"></span>
                  <span v-else>{{ stats.activeAcademicYear }}</span>
                </h6>
              </div>
              <div class="stat-icon text-primary bg-primary-subtle rounded-3 d-flex align-items-center justify-content-center">
                <i class="bi bi-calendar-check fs-5"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Section: Langkah Kerja & Info -->
      <div class="row g-3">
        <!-- Langkah Kerja -->
        <div class="col-12 col-lg-8">
          <div class="card border rounded-3 p-4 bg-white h-100">
            <h6 class="fw-bold text-dark mb-3">Langkah Kerja Penjadwalan</h6>
            <div class="d-flex flex-column gap-3">
              <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-light">
                <span class="step-num fw-bold text-primary bg-white border rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">1</span>
                <div>
                  <h6 class="fw-semibold text-dark mb-1 fs-7">Master Data Dasar</h6>
                  <p class="text-secondary small mb-0">Lengkapi data Guru, Mata Pelajaran, Slot Waktu, dan Kelas.</p>
                </div>
              </div>

              <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-light">
                <span class="step-num fw-bold text-primary bg-white border rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">2</span>
                <div>
                  <h6 class="fw-semibold text-dark mb-1 fs-7">Plotting Mapel & Guru</h6>
                  <p class="text-secondary small mb-0">Atur alokasi JP per mapel dan tetapkan guru pengampu per kelas.</p>
                </div>
              </div>

              <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-light">
                <span class="step-num fw-bold text-primary bg-white border rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">3</span>
                <div>
                  <h6 class="fw-semibold text-dark mb-1 fs-7">Auto-Generate & Adjust</h6>
                  <p class="text-secondary small mb-0">Jalankan generator otomatis, gembok slot khusus, dan sesuaikan jadwal.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Status & Quick Action -->
        <div class="col-12 col-lg-4">
          <div class="card border rounded-3 p-4 bg-white h-100 d-flex flex-column justify-content-between">
            <div>
              <h6 class="fw-bold text-dark mb-2">Status Penjadwalan</h6>
              <p class="text-secondary small mb-3">Gunakan Wizard Penjadwalan untuk menyusun atau menyesuaikan matriks jadwal aktif.</p>
              <div class="p-3 bg-light rounded-3 border">
                <small class="text-muted d-block mb-1">Periode Aktif:</small>
                <span class="fw-bold text-dark fs-7">
                  <i class="bi bi-calendar3 me-1 text-primary"></i>
                  <span v-if="isLoading" class="spinner-border spinner-border-sm text-primary ms-1" role="status"></span>
                  <span v-else>{{ stats.activeAcademicYear }}</span>
                </span>
              </div>
            </div>

            <div class="mt-4 pt-3 border-top">
              <router-link to="/admin/wizard" class="btn btn-outline-primary w-100 rounded-3 py-2 small fw-semibold d-flex align-items-center justify-content-center gap-1">
                <span>Akses Matriks Jadwal</span>
                <i class="bi bi-arrow-right"></i>
              </router-link>
            </div>
          </div>
        </div>
      </div>

    </div>
  </SidebarAdmin>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import SidebarAdmin from '../../layouts/SidebarAdmin.vue';
import { DashboardService } from '../../services/dashboard.service';

const isLoading = ref<boolean>(true);

const stats = reactive({
  teachers: '0',
  classes: '0',
  subjects: '0',
  activeAcademicYear: '-',
});

const fetchDashboardStats = async () => {
  isLoading.value = true;
  try {
    const response = await DashboardService.getStats();
    if (response.status === 'success' && response.data) {
      stats.teachers = String(response.data.teachers);
      stats.classes = String(response.data.classes);
      stats.subjects = String(response.data.subjects);
      stats.activeAcademicYear = response.data.active_academic_year || '-';
    }
  } catch (error) {
    console.error('Gagal memuat statistik dashboard:', error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchDashboardStats();
});
</script>

<style scoped>
.stat-icon {
  width: 44px;
  height: 44px;
}
.step-num {
  width: 32px;
  height: 32px;
  font-size: 0.85rem;
}
.bg-primary-subtle {
  background-color: #eff6ff !important;
  color: #2563eb !important;
}
.fs-7 {
  font-size: 0.85rem;
}
</style>