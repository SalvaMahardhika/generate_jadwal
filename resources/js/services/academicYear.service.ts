import api from './api';
import type { AcademicYear, AcademicYearPayload } from '../types/academicYear';
import type { ApiResponse } from '../types/auth';

export const AcademicYearService = {
  async getAll(search?: string): Promise<ApiResponse<AcademicYear[]>> {
    const params = search ? { search } : {};
    const response = await api.get<ApiResponse<AcademicYear[]>>('/academic-years', { params });
    return response.data;
  },

  async getById(id: number): Promise<ApiResponse<AcademicYear>> {
    const response = await api.get<ApiResponse<AcademicYear>>(`/academic-years/${id}`);
    return response.data;
  },

  async create(payload: AcademicYearPayload): Promise<ApiResponse<AcademicYear>> {
    const response = await api.post<ApiResponse<AcademicYear>>('/academic-years', payload);
    return response.data;
  },

  async update(id: number, payload: AcademicYearPayload): Promise<ApiResponse<AcademicYear>> {
    const response = await api.put<ApiResponse<AcademicYear>>(`/academic-years/${id}`, payload);
    return response.data;
  },

  async delete(id: number): Promise<ApiResponse<null>> {
    const response = await api.delete<ApiResponse<null>>(`/academic-years/${id}`);
    return response.data;
  },

  async setActive(id: number): Promise<ApiResponse<AcademicYear>> {
    const response = await api.patch<ApiResponse<AcademicYear>>(`/academic-years/${id}/set-active`);
    return response.data;
  },
};