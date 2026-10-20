<template>
  <div class="card border rounded-3 bg-white overflow-hidden shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="bg-light border-bottom">
          <tr>
            <th scope="col" class="py-3 px-3 text-muted fw-semibold small" style="width: 50px;">#</th>
            <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Mata Pelajaran</th>
            <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Beban JP/Minggu</th>
            <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Maks JP/Hari</th>
            <th scope="col" class="py-3 px-3 text-muted fw-semibold small">Guru Pengampu & Alokasi JP</th>
            <th scope="col" class="py-3 px-3 text-muted fw-semibold small text-end" style="width: 120px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!selectedClassId">
            <td colspan="6" class="text-center py-5 text-muted small">
              <i class="bi bi-arrow-up-circle fs-3 d-block mb-2 text-primary opacity-50"></i>
              Silakan pilih kelas terlebih dahulu untuk menampilkan data plotting.
            </td>
          </tr>

          <tr v-else-if="isLoading">
            <td colspan="6" class="text-center py-5 text-muted small">
              <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
              Memuat data plotting mata pelajaran...
            </td>
          </tr>

          <tr v-else-if="classSubjects.length === 0">
            <td colspan="6" class="text-center py-5 text-muted small">
              <i class="bi bi-journal-x fs-3 d-block mb-2 text-secondary opacity-50"></i>
              Belum ada mata pelajaran yang dialokasikan di kelas ini.
            </td>
          </tr>

          <tr v-else v-for="(cs, index) in classSubjects" :key="cs.id">
            <td class="px-3 small text-secondary">{{ index + 1 }}</td>
            <td class="px-3">
              <div class="d-flex align-items-center gap-2">
                <span 
                  class="color-dot rounded-circle flex-shrink-0" 
                  :style="{ backgroundColor: cs.subject?.color_code || '#cbd5e1' }"
                ></span>
                <div>
                  <div class="fw-bold text-dark small mb-0 d-flex align-items-center gap-1.5">
                    <span>{{ cs.subject?.subject_name }}</span>
                    <span 
                      v-if="cs.class_subject_teachers && cs.class_subject_teachers.length > 1" 
                      class="badge bg-info-subtle text-info border border-info-subtle fs-8 px-1.5 py-0.5 fw-medium"
                      title="Skenario Team Teaching"
                    >
                      <i class="bi bi-people-fill me-1"></i>Team
                    </span>
                  </div>
                  <small class="text-muted fs-8 font-monospace">{{ cs.subject?.subject_code }}</small>
                </div>
              </div>
            </td>
            <td class="px-3 small">
              <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fs-8 fw-bold">
                {{ cs.total_jp }} JP
              </span>
            </td>
            <td class="px-3 small text-secondary">
              {{ cs.max_jp_per_day }} JP / Hari
            </td>
            <td class="px-3 small">
              <div class="d-flex flex-wrap gap-1.5" v-if="cs.class_subject_teachers && cs.class_subject_teachers.length > 0">
                <span 
                  v-for="cst in cs.class_subject_teachers" 
                  :key="cst.id"
                  class="badge bg-light text-dark border px-2 py-1 fs-8 fw-normal d-inline-flex align-items-center gap-1"
                >
                  <i class="bi bi-person-fill text-secondary"></i>
                  <span>{{ cst.teacher?.name }}</span>
                  <span class="badge bg-secondary-subtle text-secondary border fs-8 fw-bold px-1.5 py-0.5 ms-1">
                    {{ cst.jp || 0 }} JP
                  </span>
                </span>
              </div>
              <span v-else class="text-muted fst-italic fs-8">Belum ada guru</span>
            </td>
            <td class="px-3 text-end">
              <div class="d-flex justify-content-end gap-1">
                <button 
                  type="button" 
                  class="btn btn-light btn-sm text-secondary border-0 rounded-2 px-2 py-1"
                  title="Edit Plotting"
                  @click="emit('edit', cs)"
                >
                  <i class="bi bi-pencil-square fs-7"></i>
                </button>
                <button 
                  type="button" 
                  class="btn btn-light btn-sm text-danger border-0 rounded-2 px-2 py-1"
                  title="Hapus Plotting"
                  @click="emit('delete', cs)"
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
</template>

<script setup lang="ts">
import type { ClassSubject } from '../../../../types/classSubject';

defineProps<{
  selectedClassId: number | string;
  isLoading: boolean;
  classSubjects: ClassSubject[];
}>();

const emit = defineEmits(['edit', 'delete']);
</script>

<style scoped>
.color-dot {
  width: 12px;
  height: 12px;
}
.bg-primary-subtle {
  background-color: #eff6ff !important;
  color: #2563eb !important;
}
.border-primary-subtle {
  border-color: #bfdbfe !important;
}
.bg-info-subtle {
  background-color: #f0f9ff !important;
  color: #0284c7 !important;
}
.border-info-subtle {
  border-color: #bae6fd !important;
}
.bg-secondary-subtle {
  background-color: #f1f5f9 !important;
  color: #475569 !important;
}
.fs-7 {
  font-size: 0.82rem;
}
.fs-8 {
  font-size: 0.72rem;
}
</style>