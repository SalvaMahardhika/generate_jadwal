import api from './api';
import type { Grade, GradePayload } from '../types/grade';
import type { ApiResponse } from '../types/auth';

export const GradeService = {
  async getAll(search?: string): Promise<ApiResponse<Grade[]>> {
    const params = search ? { search } : {};
    const response = await api.get<ApiResponse<Grade[]>>('/grades', { params });
    return response.data;
  },

  async getById(id: number): Promise<ApiResponse<Grade>> {
    const response = await api.get<ApiResponse<Grade>>(`/grades/${id}`);
    return response.data;
  },

  async create(payload: GradePayload): Promise<ApiResponse<Grade>> {
    const response = await api.post<ApiResponse<Grade>>('/grades', payload);
    return response.data;
  },

  async update(id: number, payload: GradePayload): Promise<ApiResponse<Grade>> {
    const response = await api.put<ApiResponse<Grade>>(`/grades/${id}`, payload);
    return response.data;
  },

  async delete(id: number): Promise<ApiResponse<null>> {
    const response = await api.delete<ApiResponse<null>>(`/grades/${id}`);
    return response.data;
  },
};