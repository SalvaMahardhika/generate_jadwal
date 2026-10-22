import api from './api';
import type {
  TimeSlotProfilePayload,
  AssignProfileToClassesPayload,
  DuplicateProfilePayload,
} from '../types/timeSlot';

export const timeSlotProfileService = {
  async getAll(academicYearId?: number) {
    const response = await api.get('/time-slot-profiles', {
      params: { academic_year_id: academicYearId },
    });
    return response.data;
  },

  async getById(id: number) {
    const response = await api.get(`/time-slot-profiles/${id}`);
    return response.data;
  },

  async create(payload: TimeSlotProfilePayload) {
    const response = await api.post('/time-slot-profiles', payload);
    return response.data;
  },

  async update(id: number, payload: TimeSlotProfilePayload) {
    const response = await api.put(`/time-slot-profiles/${id}`, payload);
    return response.data;
  },

  async delete(id: number) {
    const response = await api.delete(`/time-slot-profiles/${id}`);
    return response.data;
  },

  async assignToClasses(profileId: number, payload: AssignProfileToClassesPayload) {
    const response = await api.post(`/time-slot-profiles/${profileId}/assign-classes`, payload);
    return response.data;
  },

  async duplicate(profileId: number, payload: DuplicateProfilePayload) {
    const response = await api.post(`/time-slot-profiles/${profileId}/duplicate`, payload);
    return response.data;
  },
};