import api from './api';
import type { ClassItem, ClassPayload, ClassFilter } from '../types/class';
import type { ApiResponse } from '../types/auth';

export const ClassService = {
  async getAll(params?: ClassFilter): Promise<ApiResponse<ClassItem[]>> {
    const cleanParams: Record<string, any> = {};
    if (params?.search) cleanParams.search = params.search;
    if (params?.academic_year_id) cleanParams.academic_year_id = params.academic_year_id;
    if (params?.grade_id) cleanParams.grade_id = params.grade_id;
    if (params?.status) cleanParams.status = params.status;

    const response = await api.get<ApiResponse<ClassItem[]>>('/classes', { params: cleanParams });
    return response.data;
  },

  async getById(id: number): Promise<ApiResponse<ClassItem>> {
    const response = await api.get<ApiResponse<ClassItem>>(`/classes/${id}`);
    return response.data;
  },

  async create(payload: ClassPayload): Promise<ApiResponse<ClassItem>> {
    const response = await api.post<ApiResponse<ClassItem>>('/classes', payload);
    return response.data;
  },

  async update(id: number, payload: ClassPayload): Promise<ApiResponse<ClassItem>> {
    const response = await api.put<ApiResponse<ClassItem>>(`/classes/${id}`, payload);
    return response.data;
  },

  async delete(id: number): Promise<ApiResponse<null>> {
    const response = await api.delete<ApiResponse<null>>(`/classes/${id}`);
    return response.data;
  },
};