export interface ScheduleMatchData {
  id: number; 
  first_club_id: string;
  secound_club_id: string;
  stadium_id: string;
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
