<template>
  <div class="card border rounded-3 bg-white overflow-hidden">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="bg-light border-bottom">
          <tr>
            <th scope="col" class="py-3 px-3 text-muted fw-semibold small" style="width: 50px;">#</th>
            <th scope="col" class="py-3 px-3 text-muted fw-semibold small">NIP</th>
            <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Nama Guru</th>
            <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Gender</th>
            <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Max JP/Minggu</th>
            <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Status</th>
            <th scope="col" class="py-3 px-3 text-muted fw-semibold small text-end" style="width: 120px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <!-- Loading State -->
          <tr v-if="isLoading">
            <td colspan="7" class="text-center py-5 text-muted small">
              <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
              Memuat data guru...
            </td>
          </tr>

          <!-- Empty State -->
          <tr v-else-if="teachers.length === 0">
            <td colspan="7" class="text-center py-5 text-muted small">
              <i class="bi bi-inbox fs-3 d-block mb-2 text-secondary"></i>
              Data guru tidak ditemukan.
            </td>
          </tr>

          <!-- Data Rows -->
          <tr v-else v-for="(teacher, index) in teachers" :key="teacher.id">
            <td class="px-3 small text-secondary">{{ index + 1 }}</td>
            <td class="px-3 small font-monospace text-dark">{{ teacher.nip || '-' }}</td>
            <td class="px-3 small fw-medium text-dark">{{ teacher.name }}</td>
            <td class="px-3 small">
              <span 
                class="badge rounded-pill px-2 py-1 fw-normal fs-8"
                :class="teacher.gender === 'L' ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger'"
              >
                {{ teacher.gender === 'L' ? 'Laki-Laki' : 'Perempuan' }}
              </span>
            </td>
            <td class="px-3 small fw-semibold text-dark">{{ teacher.max_jp_per_week }} JP</td>
            <td class="px-3 small">
              <span 
                class="badge rounded-pill px-2 py-1 fw-normal fs-8"
                :class="teacher.status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'"
              >
                {{ teacher.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
              </span>
            </td>
            <td class="px-3 text-end">
              <div class="d-flex justify-content-end gap-1">
                <button 
                  type="button" 
                  class="btn btn-light btn-sm text-secondary border-0 rounded-2 px-2 py-1"
                  title="Edit"
                  @click="emit('edit', teacher)"
                >
                  <i class="bi bi-pencil-square"></i>
                </button>
                <button 
                  type="button" 
                  class="btn btn-light btn-sm text-danger border-0 rounded-2 px-2 py-1"
                  title="Hapus"
                  @click="emit('delete', teacher)"
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
import type { Teacher } from '../../../../types/teacher';

defineProps<{
  teachers: Teacher[];
  isLoading: boolean;
}>();

const emit = defineEmits(['edit', 'delete']);
</script>

<style scoped>
.bg-danger-subtle { background-color: #fef2f2 !important; color: #dc2626 !important; }
.bg-primary-subtle { background-color: #eff6ff !important; color: #2563eb !important; }
.bg-success-subtle { background-color: #f0fdf4 !important; color: #16a34a !important; }
.bg-secondary-subtle { background-color: #f1f5f9 !important; color: #64748b !important; }
.fs-8 { font-size: 0.75rem; }
</style>