<template>
  <div class="card border rounded-3 p-3 bg-white mb-4 shadow-sm">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
      <!-- Dropdown / Tab Pemilihan Profil -->
      <div class="d-flex align-items-center gap-2 overflow-auto pb-1 pb-lg-0">
        <span class="fw-semibold text-dark small text-nowrap me-1">
          <i class="bi bi-collection-fill text-primary me-1"></i> Profil Slot:
        </span>

        <div v-if="store.isLoadingProfiles" class="spinner-border spinner-border-sm text-primary" role="status"></div>

        <template v-else-if="store.profiles.length > 0">
          <div class="btn-group" role="group">
            <button
              v-for="profile in store.profiles"
              :key="profile.id"
              type="button"
              class="btn btn-sm rounded-3 me-1 text-nowrap d-flex align-items-center gap-1"
              :class="store.activeProfileId === profile.id ? 'btn-primary' : 'btn-outline-secondary'"
              @click="store.setActiveProfile(profile.id)"
            >
              <span>{{ profile.name }}</span>
              <span class="badge bg-white text-dark border fs-8 rounded-pill ms-1">
                {{ profile.classes_count || 0 }} Kelas
              </span>
            </button>
          </div>
        </template>

        <span v-else class="text-muted small italic">Belum ada profil slot waktu.</span>
      </div>

      <!-- Action Buttons Profil -->
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <button
          type="button"
          class="btn btn-sm btn-outline-primary rounded-3 d-flex align-items-center gap-1 fw-medium"
          @click="emit('openCreateProfile')"
        >
          <i class="bi bi-plus-lg"></i>
          <span>Profil Baru</span>
        </button>

        <template v-if="store.activeProfile">
          <button
            type="button"
            class="btn btn-sm btn-light border rounded-3 d-flex align-items-center gap-1 text-secondary"
            title="Edit Nama Profil Ini"
            @click="emit('openEditProfile', store.activeProfile)"
          >
            <i class="bi bi-pencil-square"></i>
            <span>Edit</span>
          </button>

          <button
            type="button"
            class="btn btn-sm btn-light border rounded-3 d-flex align-items-center gap-1 text-success"
            title="Plotting Profil Ini ke Kelas Target"
            @click="emit('openAssignClasses', store.activeProfile)"
          >
            <i class="bi bi-diagram-3-fill"></i>
            <span>Plotting Kelas</span>
          </button>

          <button
            type="button"
            class="btn btn-sm btn-light border rounded-3 d-flex align-items-center gap-1 text-danger"
            title="Hapus Profil Ini"
            :disabled="store.profiles.length <= 1"
            @click="emit('openDeleteProfile', store.activeProfile)"
          >
            <i class="bi bi-trash"></i>
          </button>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useTimeSlotStore } from '../../../../../stores/timeSlot';

const store = useTimeSlotStore();
const emit = defineEmits(['openCreateProfile', 'openEditProfile', 'openAssignClasses', 'openDeleteProfile']);
</script>

<style scoped>
.fs-8 {
  font-size: 0.7rem;
}
</style>