import api from './api';
import type { 
  ClassMatrixData, 
  TeacherMatrixData, 
  MasterMatrixData, 
  SubjectBalance,
  GenerateSchedulePayload,
  LockSlotPayload,
  ManualAssignPayload,
  ClearSlotPayload,
  ResetSchedulePayload,
  PublishSchedulePayload,
  UnpublishSchedulePayload,
  ScheduleItem,
  GenerateResult
} from '../types/wizard';
import type { ApiResponse } from '../types/auth';

export const WizardService = {
  async getClassMatrix(classId: number, academicYearId?: number): Promise<ApiResponse<ClassMatrixData>> {
    const params = academicYearId ? { academic_year_id: academicYearId } : {};
    const response = await api.get<ApiResponse<ClassMatrixData>>(`/wizard/matrix/class/${classId}`, { params });
    return response.data;
  },

  async getTeacherMatrix(teacherId: number, academicYearId: number): Promise<ApiResponse<TeacherMatrixData>> {
    const response = await api.get<ApiResponse<TeacherMatrixData>>(`/wizard/matrix/teacher/${teacherId}`, {
      params: { academic_year_id: academicYearId },
    });
    return response.data;
  },

  async getMasterMatrix(academicYearId: number): Promise<ApiResponse<MasterMatrixData>> {
    const response = await api.get<ApiResponse<MasterMatrixData>>('/wizard/matrix/master', {
      params: { academic_year_id: academicYearId },
    });
    return response.data;
  },

  async getBalance(classId: number, academicYearId?: number): Promise<ApiResponse<SubjectBalance[]>> {
    const params = academicYearId ? { academic_year_id: academicYearId } : {};
    const response = await api.get<ApiResponse<SubjectBalance[]>>(`/wizard/balance/${classId}`, { params });
    return response.data;
  },

  async generate(payload: GenerateSchedulePayload): Promise<ApiResponse<GenerateResult>> {
    const response = await api.post<ApiResponse<GenerateResult>>('/wizard/generate', payload);
    return response.data;
  },

  async lockSlot(payload: LockSlotPayload): Promise<ApiResponse<ScheduleItem>> {
    const response = await api.post<ApiResponse<ScheduleItem>>('/wizard/lock-slot', payload);
    return response.data;
  },

  async manualAssign(payload: ManualAssignPayload): Promise<ApiResponse<ScheduleItem>> {
    const response = await api.post<ApiResponse<ScheduleItem>>('/wizard/manual-assign', payload);
    return response.data;
  },

  async clearSlot(payload: ClearSlotPayload): Promise<ApiResponse<null>> {
    const response = await api.post<ApiResponse<null>>('/wizard/clear-slot', payload);
    return response.data;
  },

  async reset(payload: ResetSchedulePayload): Promise<ApiResponse<null>> {
    const response = await api.post<ApiResponse<null>>('/wizard/reset', payload);
    return response.data;
  },

  async publish(payload: PublishSchedulePayload): Promise<ApiResponse<null>> {
    const response = await api.post<ApiResponse<null>>('/wizard/publish', payload);
    return response.data;
  },

  async unpublish(payload: UnpublishSchedulePayload): Promise<ApiResponse<null>> {
    const response = await api.post<ApiResponse<null>>('/wizard/unpublish', payload);
    return response.data;
  },
};