import api from './api';
import type { Teacher, TeacherPayload, TeacherFilter } from '../types/teacher';
import type { ApiResponse } from '../types/auth';

export const TeacherService = {
  async getAll(params?: TeacherFilter): Promise<ApiResponse<Teacher[]>> {
    const response = await api.get<ApiResponse<Teacher[]>>('/teachers', { params });
    return response.data;
  },

  async getById(id: number): Promise<ApiResponse<Teacher>> {
    const response = await api.get<ApiResponse<Teacher>>(`/teachers/${id}`);
    return response.data;
  },

  async create(payload: TeacherPayload): Promise<ApiResponse<Teacher>> {
    const response = await api.post<ApiResponse<Teacher>>('/teachers', payload);
    return response.data;
  },

  async update(id: number, payload: TeacherPayload): Promise<ApiResponse<Teacher>> {
    const response = await api.put<ApiResponse<Teacher>>(`/teachers/${id}`, payload);
    return response.data;
  },

  async delete(id: number): Promise<ApiResponse<null>> {
    const response = await api.delete<ApiResponse<null>>(`/teachers/${id}`);
    return response.data;
  },
};