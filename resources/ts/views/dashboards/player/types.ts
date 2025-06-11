export interface Club {
  id: number;
  code: string;
  name: string;
}

export interface ClubPlayer {
  back_number: string;   // atau number, sesuaikan dengan API
  position: string;
  is_captain: boolean;   // atau bisa number (0/1) jika API kirim seperti itu
  status: boolean;
  club?: Club;           // jika relasi club di-load pada ClubPlayerResource
}

export interface Sport {
  id: number;
  code: string;
  name: string;
}

export interface SportPlayer {
  id: number;
  player_id: string;
  sport_id: Sport['id'];
}

export interface PlayerData {
  id: number;
  email: string;
  password?: string; // ← sekarang opsional, untuk update
  name: string;
  nisn: string;
  height: string;
  weight: string;
  user?: string; // ← sekarang opsional
  clubs?: Club[];
  club_players?: ClubPlayer[];
  sports?: Sport[];
  sport_players?: SportPlayer[];
  family_card?: File;
  report_grades?: File;
  birth_certificate?: File;
  avatar?: File; 
}

