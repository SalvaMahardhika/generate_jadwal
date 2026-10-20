<template>
  <Teleport to="body">
    <div v-if="show" class="modal-backdrop fade show"></div>
    <div v-if="show" class="modal fade show d-block" tabindex="-1" @click.self="emit('close')">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden bg-white">
          <div class="modal-body text-center p-4">
            
            <div class="delete-icon mx-auto mb-3 text-danger bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center">
              <i class="bi bi-exclamation-triangle fs-4"></i>
            </div>
            
            <h6 class="fw-bold text-dark mb-1">{{ isClearDay ? 'Hapus Semua Slot Hari Ini?' : 'Hapus Slot Waktu?' }}</h6>
            <p class="text-secondary small mb-3">
              <span v-if="isClearDay">
                Apakah Anda yakin ingin menghapus <strong>seluruh slot waktu</strong> di hari <strong class="text-capitalize">{{ targetDay }}</strong>?
              </span>
              <span v-else-if="slot">
                Apakah Anda yakin ingin menghapus slot waktu <strong class="text-capitalize">{{ slot.day }} - JP {{ slot.period_number }}</strong>?
              </span>
            </p>

            <div v-if="errorMessage" class="alert alert-danger border-0 rounded-3 py-2 px-3 small text-start mb-3" role="alert">
              {{ errorMessage }}
            </div>

            <div class="d-flex gap-2">
              <button 
                type="button" 
                class="btn btn-light w-50 rounded-3 py-2 small fw-medium border shadow-none"
                @click="emit('close')"
                :disabled="isSaving"
              >
                Batal
              </button>
              <button 
                type="button" 
                class="btn btn-danger w-50 rounded-3 py-2 small fw-semibold shadow-none d-flex align-items-center justify-content-center gap-2"
                @click="emit('confirm')"
                :disabled="isSaving"
              >
                <span v-if="!isSaving">Hapus</span>
                <span v-else class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              </button>
            </div>

          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import type { TimeSlot, DayOfWeek } from '../../../../types/timeSlot';

defineProps<{
  show: boolean;
  slot: TimeSlot | null;
  targetDay?: DayOfWeek | null;
  isClearDay?: boolean;
  isSaving: boolean;
  errorMessage: string | null;
}>();

const emit = defineEmits(['close', 'confirm']);
</script>

<style scoped>
.delete-icon {
  width: 48px;
  height: 48px;
}
.bg-danger-subtle {
  background-color: #fef2f2 !important;
  color: #dc2626 !important;
}
</style>