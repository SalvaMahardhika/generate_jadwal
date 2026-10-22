import api from './api';
import type { TimeSlot, TimeSlotPayload, TimeSlotFilter, GenerateTimeSlotPayload } from '../types/timeSlot';
import type { ApiResponse } from '../types/auth';

export const TimeSlotService = {
  async getAll(params?: TimeSlotFilter): Promise<ApiResponse<TimeSlot[]>> {
    const cleanParams: Record<string, any> = {};

    if (params?.time_slot_profile_id) {
      cleanParams.time_slot_profile_id = params.time_slot_profile_id;
    }

    if (params?.day) {
      cleanParams.day = params.day;
    }

    if (params?.is_break !== undefined && params?.is_break !== null && params?.is_break !== '') {
      cleanParams.is_break = params.is_break;
    }

    const response = await api.get<ApiResponse<TimeSlot[]>>('/time-slots', { params: cleanParams });
    return response.data;
  },

  async getById(id: number): Promise<ApiResponse<TimeSlot>> {
    const response = await api.get<ApiResponse<TimeSlot>>(`/time-slots/${id}`);
    return response.data;
  },

  async create(payload: TimeSlotPayload): Promise<ApiResponse<TimeSlot>> {
    const response = await api.post<ApiResponse<TimeSlot>>('/time-slots', payload);
    return response.data;
  },

  async update(id: number, payload: TimeSlotPayload): Promise<ApiResponse<TimeSlot>> {
    const response = await api.put<ApiResponse<TimeSlot>>(`/time-slots/${id}`, payload);
    return response.data;
  },

  async delete(id: number): Promise<ApiResponse<null>> {
    const response = await api.delete<ApiResponse<null>>(`/time-slots/${id}`);
    return response.data;
  },

  /**
   * Menghapus seluruh slot waktu pada hari tertentu untuk profil tertentu.
   */
  async deleteByDay(day: string, profileId?: number | null): Promise<ApiResponse<null>> {
    const response = await api.delete<ApiResponse<null>>(`/time-slots/day/${day}`, {
      params: profileId ? { time_slot_profile_id: profileId } : undefined,
    });
    return response.data;
  },

  /**
   * Mengirim parameter konfigurasi untuk auto-generate slot waktu.
   */
  async generate(payload: GenerateTimeSlotPayload): Promise<ApiResponse<TimeSlot[]>> {
    const response = await api.post<ApiResponse<TimeSlot[]>>('/time-slots/generate', payload);
    return response.data;
  },
};