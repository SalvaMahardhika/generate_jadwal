import api from './api';
import type { LoginCredentials, AuthResponseData, ApiResponse, User } from '../types/auth';

export const AuthService = {
  async login(credentials: LoginCredentials): Promise<ApiResponse<AuthResponseData>> {
    const response = await api.post<ApiResponse<AuthResponseData>>('/auth/login', credentials);
    return response.data;
  },

  async me(): Promise<ApiResponse<User>> {
    const response = await api.get<ApiResponse<User>>('/auth/me');
    return response.data;
  },

  async logout(): Promise<ApiResponse<null>> {
    const response = await api.post<ApiResponse<null>>('/auth/logout');
    return response.data;
  },
};