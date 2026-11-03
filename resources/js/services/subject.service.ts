import api from './api';
import type { Subject, SubjectPayload, SubjectFilter, ImportSubjectPayload } from '../types/subject';
import type { ApiResponse } from '../types/auth';

export const SubjectService = {
  async getAll(params?: SubjectFilter): Promise<ApiResponse<Subject[]>> {
    const response = await api.get<ApiResponse<Subject[]>>('/subjects', { params });
    return response.data;
  },

  async getById(id: number): Promise<ApiResponse<Subject>> {
    const response = await api.get<ApiResponse<Subject>>(`/subjects/${id}`);
    return response.data;
  },

  async create(payload: SubjectPayload): Promise<ApiResponse<Subject>> {
    const response = await api.post<ApiResponse<Subject>>('/subjects', payload);
    return response.data;
  },

  async update(id: number, payload: SubjectPayload): Promise<ApiResponse<Subject>> {
    const response = await api.put<ApiResponse<Subject>>(`/subjects/${id}`, payload);
    return response.data;
  },

  async delete(id: number): Promise<ApiResponse<null>> {
    const response = await api.delete<ApiResponse<null>>(`/subjects/${id}`);
    return response.data;
  },

  async import(subjects: SubjectPayload[]): Promise<ApiResponse<Subject[]>> {
    const response = await api.post<ApiResponse<Subject[]>>('/subjects/import', { subjects } as ImportSubjectPayload);
    return response.data;
  },
};