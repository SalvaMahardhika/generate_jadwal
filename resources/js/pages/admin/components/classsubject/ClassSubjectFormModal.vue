<template>
  <Teleport to="body">
    <div v-if="show" class="modal-backdrop fade show"></div>
    <div v-if="show" class="modal fade show d-block" tabindex="-1" @click.self="emit('close')">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden bg-white">
          
          <div class="modal-header border-bottom px-4 py-3">
            <div>
              <h6 class="modal-title fw-bold text-dark mb-0">
                {{ isEditing ? 'Edit Plotting Mapel & Team Teaching' : 'Assign Mapel & Guru ke Kelas' }}
              </h6>
              <small class="text-secondary fs-8">Atur beban JP dan alokasikan guru pengampu tunggal maupun team teaching.</small>
            </div>
            <button type="button" class="btn-close small shadow-none" @click="emit('close')" :disabled="isSaving"></button>
          </div>

          <form @submit.prevent="handleSubmit">
            <div class="modal-body p-4">
              
              <!-- Pilih Mata Pelajaran (Hanya saat Tambah Baru) -->
              <div class="mb-3" v-if="!isEditing">
                <label for="subject_id" class="form-label fw-medium text-dark small">Mata Pelajaran <span class="text-danger">*</span></label>
                <select 
                  id="subject_id" 
                  class="form-select form-select-sm rounded-3 shadow-none"
                  :class="{ 'is-invalid': errors.subject_id }"
                  v-model="form.subject_id"
                  required
                >
                  <option value="">-- Pilih Mata Pelajaran --</option>
                  <option v-for="subject in unassignedSubjects" :key="subject.id" :value="subject.id">
                    {{ subject.subject_code }} - {{ subject.subject_name }}
                  </option>
                </select>
                <div v-if="errors.subject_id" class="invalid-feedback small">{{ errors.subject_id[0] }}</div>
              </div>

              <div class="mb-3" v-else>
                <label class="form-label fw-medium text-dark small">Mata Pelajaran</label>
                <input 
                  type="text" 
                  class="form-control form-control-sm rounded-3 bg-light text-dark fw-bold" 
                  :value="selectedCS?.subject?.subject_name" 
                  disabled
                >
              </div>

              <!-- Total JP & Maks JP Per Hari -->
              <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6">
                  <label for="total_jp" class="form-label fw-medium text-dark small">Total JP Mapel / Minggu <span class="text-danger">*</span></label>
                  <input 
                    type="number" 
                    id="total_jp" 
                    class="form-control form-control-sm rounded-3 shadow-none"
                    :class="{ 'is-invalid': errors.total_jp }"
                    placeholder="Contoh: 4"
                    v-model.number="form.total_jp"
                    min="1"
                    required
                  >
                  <div v-if="errors.total_jp" class="invalid-feedback small">{{ errors.total_jp[0] }}</div>
                </div>

                <div class="col-12 col-sm-6">
                  <label for="max_jp_per_day" class="form-label fw-medium text-dark small">Maks JP / Hari <span class="text-danger">*</span></label>
                  <input 
                    type="number" 
                    id="max_jp_per_day" 
                    class="form-control form-control-sm rounded-3 shadow-none"
                    :class="{ 'is-invalid': errors.max_jp_per_day }"
                    placeholder="Contoh: 2"
                    v-model.number="form.max_jp_per_day"
                    min="1"
                    required
                  >
                  <div v-if="errors.max_jp_per_day" class="invalid-feedback small">{{ errors.max_jp_per_day[0] }}</div>
                </div>
              </div>

              <!-- Section Team Teaching: Pilihan Guru & Input JP -->
              <div class="mb-2">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <label class="form-label fw-medium text-dark small mb-0">
                    Guru Pengampu & Pembagian JP (Team Teaching) <span class="text-danger">*</span>
                  </label>

                  <button 
                    type="button" 
                    class="btn btn-sm btn-outline-primary rounded-3 fs-8 py-0.5 px-2 fw-medium shadow-none"
                    :disabled="form.teachers.length === 0 || !form.total_jp"
                    @click="autoDistributeJp"
                    title="Bagi rata JP ke seluruh guru yang dipilih"
                  >
                    <i class="bi bi-calculator me-1"></i>Bagi Rata JP
                  </button>
                </div>

                <!-- Box Daftar Pilihan Guru -->
                <div 
                  class="border rounded-3 p-3 bg-light overflow-y-auto"
                  :class="{ 'border-danger': errors.teachers }"
                  style="max-height: 220px;"
                >
                  <div v-if="teachersList.length === 0" class="text-muted small">Data guru tidak ditemukan.</div>
                  <div 
                    v-else 
                    v-for="teacher in teachersList" 
                    :key="teacher.id"
                    class="p-2 bg-white rounded-3 border mb-2 d-flex align-items-center justify-content-between gap-3"
                  >
                    <div class="form-check mb-0">
                      <input 
                        class="form-check-input shadow-none" 
                        type="checkbox" 
                        :id="`teacher_${teacher.id}`"
                        :checked="isTeacherSelected(teacher.id)"
                        @change="toggleTeacher(teacher.id)"
                      >
                      <label class="form-check-label small text-dark fw-medium" :for="`teacher_${teacher.id}`">
                        {{ teacher.name }} 
                        <span class="text-muted fs-8 font-monospace" v-if="teacher.nip">({{ teacher.nip }})</span>
                      </label>
                    </div>

                    <div class="d-flex align-items-center gap-1" style="width: 130px;">
                      <span class="fs-8 text-muted">Beban:</span>
                      <input 
                        type="number" 
                        min="1"
                        class="form-control form-control-sm text-center py-0 px-1 shadow-none rounded-2"
                        :disabled="!isTeacherSelected(teacher.id)"
                        :value="getTeacherJp(teacher.id)"
                        @input="updateTeacherJp(teacher.id, Number(($event.target as HTMLInputElement).value))"
                      >
                      <span class="fs-8 text-muted">JP</span>
                    </div>
                  </div>
                </div>

                <!-- Indikator Validasi Realtime Total Alokasi JP Guru -->
                <div class="d-flex align-items-center justify-content-between mt-2 px-1">
                  <div class="fs-8 fw-medium">
                    <span>Total Alokasi JP Guru: </span>
                    <span 
                      class="fw-bold" 
                      :class="allocatedJpSummary.isMatch ? 'text-success' : 'text-danger'"
                    >
                      {{ allocatedJpSummary.total }} / {{ form.total_jp || 0 }} JP
                    </span>
                  </div>

                  <span v-if="allocatedJpSummary.isMatch" class="badge bg-success-subtle text-success border border-success-subtle fs-8">
                    <i class="bi bi-check-circle-fill me-1"></i>Alokasi Pas
                  </span>
                  <span v-else class="badge bg-danger-subtle text-danger border border-danger-subtle fs-8">
                    <i class="bi bi-exclamation-circle-fill me-1"></i>
                    {{ allocatedJpSummary.total > Number(form.total_jp) ? 'Kelebihan JP' : 'Kurang JP' }}
                  </span>
                </div>

                <div v-if="errors.teachers" class="text-danger small mt-1 fs-8">{{ errors.teachers[0] }}</div>
              </div>

            </div>

            <div class="modal-footer border-top px-4 py-3 bg-light">
              <button 
                type="button" 
                class="btn btn-light rounded-3 py-2 px-3 small fw-medium border shadow-none"
                @click="emit('close')"
                :disabled="isSaving"
              >
                Batal
              </button>
              <button 
                type="submit" 
                class="btn btn-primary rounded-3 py-2 px-3 small fw-semibold shadow-none d-flex align-items-center gap-2"
                :disabled="isSaving || !allocatedJpSummary.isMatch || form.teachers.length === 0"
              >
                <span v-if="!isSaving">Simpan</span>
                <span v-else class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { Subject } from '../../../../types/subject';
import type { TeacherOption } from '../../../../types/class';
import type { ClassSubject, TeacherAssignmentInput } from '../../../../types/classSubject';

const props = defineProps<{
  show: boolean;
  isEditing: boolean;
  isSaving: boolean;
  selectedCS: ClassSubject | null;
  unassignedSubjects: Subject[];
  teachersList: TeacherOption[];
  form: {
    subject_id: number | string;
    total_jp: number | string;
    max_jp_per_day: number | string;
    teachers: TeacherAssignmentInput[];
  };
  errors: Record<string, string[]>;
}>();

const emit = defineEmits(['close', 'submit']);

const allocatedJpSummary = computed(() => {
  const total = props.form.teachers.reduce((sum, item) => sum + (Number(item.jp) || 0), 0);
  const target = Number(props.form.total_jp) || 0;
  return {
    total,
    target,
    isMatch: target > 0 && total === target,
  };
});

const isTeacherSelected = (teacherId: number): boolean => {
  return props.form.teachers.some(t => t.teacher_id === teacherId);
};

const getTeacherJp = (teacherId: number): number => {
  const item = props.form.teachers.find(t => t.teacher_id === teacherId);
  return item ? item.jp : 0;
};

const toggleTeacher = (teacherId: number) => {
  const index = props.form.teachers.findIndex(t => t.teacher_id === teacherId);
  if (index > -1) {
    props.form.teachers.splice(index, 1);
  } else {
    props.form.teachers.push({ teacher_id: teacherId, jp: 1 });
  }
};

const updateTeacherJp = (teacherId: number, jp: number) => {
  const item = props.form.teachers.find(t => t.teacher_id === teacherId);
  if (item) {
    item.jp = jp > 0 ? jp : 1;
  }
};

const autoDistributeJp = () => {
  const teacherCount = props.form.teachers.length;
  const totalJp = Number(props.form.total_jp) || 0;
  if (teacherCount === 0 || totalJp <= 0) return;

  const baseJp = Math.floor(totalJp / teacherCount);
  let remainder = totalJp % teacherCount;

  props.form.teachers.forEach((t) => {
    t.jp = baseJp + (remainder > 0 ? 1 : 0);
    if (remainder > 0) remainder--;
  });
};

const handleSubmit = () => {
  emit('submit');
};
</script>

<style scoped>
.fs-8 {
  font-size: 0.72rem;
}
.bg-success-subtle {
  background-color: #f0fdf4 !important;
  color: #166534 !important;
}
.border-success-subtle {
  border-color: #bbf7d0 !important;
}
.bg-danger-subtle {
  background-color: #fef2f2 !important;
  color: #991b1b !important;
}
.border-danger-subtle {
  border-color: #fecaca !important;
}
</style>