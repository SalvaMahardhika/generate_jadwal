<template>
  <div class="card border rounded-3 bg-white overflow-hidden">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="bg-light border-bottom">
          <tr>
            <th scope="col" class="py-3 px-3 text-muted fw-semibold small" style="width: 50px;">#</th>
            <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Kode</th>
            <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Nama Mata Pelajaran</th>
            <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Kategori</th>
            <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Tipe Pelaksanaan</th>
            <th scope="col" class="py-3 px-3 text-muted fw-semibold small">KKM</th>
            <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Status</th>
            <th scope="col" class="py-3 px-3 text-muted fw-semibold small text-end" style="width: 120px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <!-- Loading -->
          <tr v-if="isLoading">
            <td colspan="8" class="text-center py-5 text-muted small">
              <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
              Memuat data mata pelajaran...
            </td>
          </tr>

          <!-- Empty -->
          <tr v-else-if="subjects.length === 0">
            <td colspan="8" class="text-center py-5 text-muted small">
              <i class="bi bi-inbox fs-3 d-block mb-2 text-secondary"></i>
              Data mata pelajaran tidak ditemukan.
            </td>
          </tr>

          <!-- Rows -->
          <tr v-else v-for="(subject, index) in subjects" :key="subject.id">
            <td class="px-3 small text-secondary">{{ index + 1 }}</td>
            <td class="px-3 small font-monospace fw-bold text-dark">
              <span class="d-inline-block rounded-circle me-1" :style="{ backgroundColor: subject.color_code || '#6c757d', width: '8px', height: '8px' }"></span>
              {{ subject.subject_code }}
            </td>
            <td class="px-3 small fw-medium text-dark">
              {{ subject.subject_name }}
            </td>
            <td class="px-3 small text-secondary text-capitalize">{{ subject.category || '-' }}</td>
            <td class="px-3 small">
              <span 
                v-if="subject.is_joint_subject" 
                class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-1 fs-8 fw-medium d-inline-flex align-items-center gap-1"
                title="Guru dapat mengampu beberapa kelas bersamaan di slot waktu yang sama"
              >
                <i class="bi bi-people-fill"></i> Mapel Bersama
              </span>
              <span v-else class="text-muted small">
                Reguler
              </span>
            </td>
            <td class="px-3 small fw-semibold text-dark">{{ subject.passing_grade || '-' }}</td>
            <td class="px-3 small">
              <span 
                class="badge rounded-pill px-2 py-1 fw-normal fs-8"
                :class="subject.status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'"
              >
                {{ subject.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
              </span>
            </td>
            <td class="px-3 text-end">
              <div class="d-flex justify-content-end gap-1">
                <button 
                  type="button" 
                  class="btn btn-light btn-sm text-secondary border-0 rounded-2 px-2 py-1"
                  title="Edit"
                  @click="emit('edit', subject)"
                >
                  <i class="bi bi-pencil-square"></i>
                </button>
                <button 
                  type="button" 
                  class="btn btn-light btn-sm text-danger border-0 rounded-2 px-2 py-1"
                  title="Hapus"
                  @click="emit('delete', subject)"
                >
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Subject } from '../../../../types/subject';

defineProps<{
  subjects: Subject[];
  isLoading: boolean;
}>();

const emit = defineEmits<{
  (e: 'edit', subject: Subject): void;
  (e: 'delete', subject: Subject): void;
}>();
</script>

<style scoped>
.bg-success-subtle {
  background-color: #f0fdf4 !important;
  color: #16a34a !important;
}
.bg-secondary-subtle {
  background-color: #f1f5f9 !important;
  color: #64748b !important;
}
.bg-info-subtle {
  background-color: #e0f2fe !important;
  color: #0284c7 !important;
}
.fs-8 {
  font-size: 0.75rem;
}
</style>