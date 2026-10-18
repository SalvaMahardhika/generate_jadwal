export interface Subject {
  id: number;
  subject_code: string;
  subject_name: string;
  color_code?: string | null;
  category?: string | null;
  passing_grade?: number | null;
  status: 'active' | 'inactive';
  created_at?: string;
  updated_at?: string;
}

export interface SubjectPayload {
  subject_code: string;
  subject_name: string;
  color_code?: string | null;
  category?: string | null;
  passing_grade?: number | null;
  status?: 'active' | 'inactive';
}

export interface SubjectFilter {
  search?: string;
  category?: string;
  status?: string;
}