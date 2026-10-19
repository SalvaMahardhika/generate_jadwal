<template>
  <div class="card border rounded-3 bg-white shadow-sm h-100">
    <div class="card-header bg-light border-bottom py-3 px-3">
      <h6 class="fw-bold text-dark mb-0 fs-7">
        <i class="bi bi-pie-chart-fill text-primary me-2"></i> Saldo Beban JP Kelas
      </h6>
    </div>

    <div class="card-body p-3 overflow-y-auto" style="max-height: 600px;">
      <div v-if="!balances || balances.length === 0" class="text-center py-4 text-muted small">
        Tidak ada data mapel teralokasi.
      </div>

      <div v-else class="d-flex flex-column gap-2.5">
        <div 
          v-for="item in balances" 
          :key="item.class_subject_id"
          class="p-2.5 rounded-3 border bg-light position-relative overflow-hidden"
        >
          <div 
            class="position-absolute top-0 start-0 bottom-0" 
            :style="{ width: '4px', backgroundColor: item.color_code || '#cbd5e1' }"
          ></div>

          <div class="ps-2">
            <div class="d-flex justify-content-between align-items-start mb-1">
              <div>
                <span class="fw-bold text-dark small d-block">{{ item.subject_name }}</span>
                <span class="text-muted fs-8 font-monospace">{{ item.subject_code }}</span>
              </div>

              <span 
                class="badge rounded-pill px-2 py-0.5 fs-8 fw-bold ms-2"
                :class="{
                  'bg-success-subtle text-success border border-success-subtle': item.status === 'balanced',
                  'bg-warning-subtle text-warning border border-warning-subtle': item.status === 'under',
                  'bg-danger-subtle text-danger border border-danger-subtle': item.status === 'over',
                }"
              >
                {{ item.assigned_jp }} / {{ item.target_jp }} JP
              </span>
            </div>

            <small class="text-secondary d-block fs-8 text-truncate mb-1.5" v-if="item.teachers">
              <i class="bi bi-person me-1"></i> {{ item.teachers }}
            </small>

            <!-- Progress Bar -->
            <div class="progress" style="height: 4px;">
              <div 
                class="progress-bar"
                :class="{
                  'bg-success': item.status === 'balanced',
                  'bg-warning': item.status === 'under',
                  'bg-danger': item.status === 'over',
                }"
                :style="{ width: `${Math.min(100, (item.assigned_jp / item.target_jp) * 100)}%` }"
              ></div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { SubjectBalance } from '../../../../types/wizard';

defineProps<{
  balances: SubjectBalance[];
}>();
</script>

<style scoped>
.fs-7 {
  font-size: 0.85rem;
}
.fs-8 {
  font-size: 0.72rem;
}
.bg-success-subtle { background-color: #f0fdf4 !important; color: #16a34a !important; }
.bg-warning-subtle { background-color: #fefce8 !important; color: #ca8a04 !important; }
.bg-danger-subtle { background-color: #fef2f2 !important; color: #dc2626 !important; }
.border-success-subtle { border-color: #bbf7d0 !important; }
.border-warning-subtle { border-color: #fef08a !important; }
.border-danger-subtle { border-color: #fecaca !important; }
</style>