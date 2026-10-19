import api from './api';
import type { User, UserPayload, UserFilter } from '../types/user';
import type { ApiResponse } from '../types/auth';

export const UserService = {
  async getAll(params?: UserFilter): Promise<ApiResponse<User[]>> {
    const cleanParams: Record<string, any> = {};
    if (params?.search) cleanParams.search = params.search;
    if (params?.status) cleanParams.status = params.status;

    const response = await api.get<ApiResponse<User[]>>('/users', { params: cleanParams });
    return response.data;
  },

  async getById(id: number): Promise<ApiResponse<User>> {
    const response = await api.get<ApiResponse<User>>(`/users/${id}`);
    return response.data;
  },

  async create(payload: UserPayload): Promise<ApiResponse<User>> {
    const response = await api.post<ApiResponse<User>>('/users', payload);
    return response.data;
  },

  async update(id: number, payload: UserPayload): Promise<ApiResponse<User>> {
    const response = await api.put<ApiResponse<User>>(`/users/${id}`, payload);
    return response.data;
  },

  async delete(id: number): Promise<ApiResponse<null>> {
    const response = await api.delete<ApiResponse<null>>(`/users/${id}`);
    return response.data;
  },
};