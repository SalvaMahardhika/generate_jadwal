export type DayOfWeek = 'senin' | 'selasa' | 'rabu' | 'kamis' | 'jumat' | 'sabtu';

export interface TimeSlot {
  id: number;
  day: DayOfWeek;
  period_number: number;
  label?: string | null;
  start_time: string;
  end_time: string;
  is_break: boolean;
  created_at?: string;
  updated_at?: string;
}

export interface TimeSlotPayload {
  day: DayOfWeek;
  period_number: number;
  label?: string | null;
  start_time: string;
  end_time: string;
  is_break: boolean;
}

export interface TimeSlotFilter {
  day?: string;
  is_break?: string | boolean;
}