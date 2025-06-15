export interface ScheduleMatchData {
  id: number; 
  first_club_id: number;
  secound_club_id: number;
  stadium_id: number;
  schedule_date: string;
  schedule_start_at: string;
  schedule_end_at: string;
  score: string;
  status: boolean;
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
