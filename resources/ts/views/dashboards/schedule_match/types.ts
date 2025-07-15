export interface ScheduleMatchData {
  id: number; 
  first_club_id: string;
  secound_club_id: string;
  stadium_id: string;
  schedule_date: string;
  schedule_start_at: string;
  schedule_end_at: string;
  first_club_score?: string | null;
  secound_club_score?: string | null;
  status?: string | null;
}

export interface Club {
  id: number;
  code: string;
  name: string;
}

export interface Stadium {
  id: number;
  code: string;
  name: string;
}
