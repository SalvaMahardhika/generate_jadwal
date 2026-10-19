import api from './api';
import type { ApiResponse } from '../types/auth';

export interface DashboardStats {
  teachers: number;
  classes: number;
  subjects: number;
  active_academic_year: string;
}

export const DashboardService = {
  async getStats(): Promise<ApiResponse<DashboardStats>> {
    const response = await api.get<ApiResponse<DashboardStats>>('/dashboard/stats');
    return response.data;
  },
};