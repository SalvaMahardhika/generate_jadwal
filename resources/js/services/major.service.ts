import api from './api';
import type { Major, MajorPayload, MajorFilter } from '../types/major';
import type { ApiResponse } from '../types/auth';

export const MajorService = {
  async getAll(params?: MajorFilter): Promise<ApiResponse<Major[]>> {
    const cleanParams: Record<string, any> = {};
    if (params?.search) cleanParams.search = params.search;
    if (params?.status) cleanParams.status = params.status;

    const response = await api.get<ApiResponse<Major[]>>('/majors', { params: cleanParams });
    return response.data;
  },

  async getById(id: number): Promise<ApiResponse<Major>> {
    const response = await api.get<ApiResponse<Major>>(`/majors/${id}`);
    return response.data;
  },

  async create(payload: MajorPayload): Promise<ApiResponse<Major>> {
    const response = await api.post<ApiResponse<Major>>('/majors', payload);
    return response.data;
  },

  async update(id: number, payload: MajorPayload): Promise<ApiResponse<Major>> {
    const response = await api.put<ApiResponse<Major>>(`/majors/${id}`, payload);
    return response.data;
  },

  async delete(id: number): Promise<ApiResponse<null>> {
    const response = await api.delete<ApiResponse<null>>(`/majors/${id}`);
    return response.data;
  },
};