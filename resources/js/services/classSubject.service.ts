import api from './api';
import type { 
  ClassSubject, 
  ClassSubjectGroupData, 
  AssignClassSubjectPayload, 
  UpdateClassSubjectPayload 
} from '../types/classSubject';
import type { ApiResponse } from '../types/auth';

export const ClassSubjectService = {
  async getByClass(classId: number): Promise<ApiResponse<ClassSubjectGroupData>> {
    const response = await api.get<ApiResponse<ClassSubjectGroupData>>(`/classes/${classId}/subjects`);
    return response.data;
  },

  async assignSubject(classId: number, payload: AssignClassSubjectPayload): Promise<ApiResponse<ClassSubject>> {
    const response = await api.post<ApiResponse<ClassSubject>>(`/classes/${classId}/subjects`, payload);
    return response.data;
  },

  async updateAssignment(id: number, payload: UpdateClassSubjectPayload): Promise<ApiResponse<ClassSubject>> {
    const response = await api.put<ApiResponse<ClassSubject>>(`/class-subjects/${id}`, payload);
    return response.data;
  },

  async deleteAssignment(id: number): Promise<ApiResponse<null>> {
    const response = await api.delete<ApiResponse<null>>(`/class-subjects/${id}`);
    return response.data;
  },
};